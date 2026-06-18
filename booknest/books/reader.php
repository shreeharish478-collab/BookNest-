<?php
// books/reader.php
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$book_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

if ($book_id === 0) {
    die("Invalid Book.");
}

$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$book_id]);
$book = $stmt->fetch();

if (!$book || empty($book['pdf_file'])) {
    die("Book or PDF file not found.");
}

$pdf_file = $book['pdf_file'];

// Sanitize backslashes to forward slashes (fixes Windows path issues)
$pdf_file = str_replace('\\', '/', $pdf_file);

// Support both database formats: only filename or full relative path
$is_relative = (strpos($pdf_file, 'uploads/') !== false);

$base_dir = realpath(__DIR__ . '/..'); // Navigate up to 'booknest' root folder

if ($is_relative) {
    // Format: 'uploads/books/book.pdf'
    // The reader is in /books/, so to get to /uploads/, we go up one directory
    $pdf_url = '../' . ltrim($pdf_file, '/');
    $server_path = $base_dir . '/' . ltrim($pdf_file, '/');
} else {
    // Format: 'book.pdf'
    $pdf_url = '../uploads/books/' . ltrim($pdf_file, '/');
    $server_path = $base_dir . '/uploads/books/' . ltrim($pdf_file, '/');
}

// Convert backslashes for exact file_exists matching on Windows/Linux
$server_path = str_replace('\\', '/', $server_path);

// If the file does not exist on the server, show a clear error with the attempted paths
if (!file_exists($server_path)) {
    die("
        <div style='font-family: sans-serif; background: #1e1e1e; color: white; padding: 40px; text-align: center; height: 100vh;'>
            <h2 style='color: #dc3545;'>Error: PDF File Not Found</h2>
            <p>The system could not locate the PDF file for this book.</p>
            <div style='background: #2c2c2c; padding: 20px; border-radius: 8px; display: inline-block; text-align: left; margin-top: 20px; font-family: monospace; color: #ffc107;'>
                <p><strong>Attempted Browser URL:</strong><br> " . htmlspecialchars($pdf_url) . "</p>
                <p><strong>Attempted Server Path:</strong><br> " . htmlspecialchars($server_path) . "</p>
            </div>
            <br><br>
            <a href='javascript:history.back()' style='color: #0d6efd; text-decoration: none;'>&larr; Go Back</a>
        </div>
    ");
}

// Fetch last reading progress
$prog_stmt = $pdo->prepare("SELECT last_page FROM reading_progress WHERE user_id = ? AND book_id = ?");
$prog_stmt->execute([$user_id, $book_id]);
$prog = $prog_stmt->fetch();
$last_page = $prog ? $prog['last_page'] : 1;

?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark"> <!-- Force dark theme for reader ideally -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reading: <?php echo htmlspecialchars($book['title']); ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- PDF.js modern stable version -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <style>
        body { background-color: #1a1a1a; color: #fff; margin: 0; display: flex; flex-direction: column; height: 100vh; overflow: hidden; perspective: 1500px; }
        #toolbar { background-color: #242424; padding: 12px 25px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 15px rgba(0,0,0,0.6); z-index: 100; position: relative;}
        
        /* BookNest Signature 3D Reader Styles */
        #book-container { 
            flex-grow: 1; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            background: linear-gradient(135deg, #121212 0%, #1e1e1e 100%); 
            padding: 20px; 
            overflow: hidden;
            position: relative;
        }
        
        .book-wrapper {
            position: relative;
            width: auto;
            height: 85vh;
            display: flex;
            box-shadow: 0 0 30px rgba(0,0,0,0.8);
            transform-style: preserve-3d;
            transition: transform 0.5s cubic-bezier(0.645, 0.045, 0.355, 1);
        }

        .page {
            background-color: #fafafa;
            height: 100%;
            position: relative;
            transform-origin: left center;
            transition: transform 0.6s cubic-bezier(0.645, 0.045, 0.355, 1);
            /* Soft paper texture simulation */
            box-shadow: inset 5px 0 20px rgba(0,0,0,0.1), inset -1px 0 2px rgba(255,255,255,0.8);
            backface-visibility: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }
        
        .page canvas {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            mix-blend-mode: multiply; /* Makes white background of PDF blend slightly with paper */
        }

        /* Page Turn Animation Classes */
        .page.turning-next {
            transform: rotateY(-180deg);
            z-index: 50;
        }
        
        .page.turning-prev {
            transform: rotateY(180deg) translateZ(-1px);
            z-index: 50;
        }

        /* Toolbar styling */
        .btn-toolbar { background-color: #383838; color: #e0e0e0; border: 1px solid #4d4d4d; padding: 8px 18px; margin: 0 5px; border-radius: 6px; cursor: pointer; transition: all 0.2s; font-weight: 500;}
        .btn-toolbar:hover { background-color: #4a4a4a; color: #fff; border-color: #666; transform: translateY(-1px);}
        .btn-toolbar:disabled { background-color: #2a2a2a; color: #555; border-color: #333; cursor: not-allowed; transform: none;}
        #page-num-input { width: 55px; text-align: center; background: #2a2a2a; color: white; border: 1px solid #555; border-radius: 4px; padding: 4px; font-weight: bold;}
        
        /* Loading Overlay */
        #loader { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); display: flex; flex-direction: column; align-items: center; z-index: 200; }
        .spinner { width: 40px; height: 40px; border: 4px solid rgba(255,255,255,0.1); border-left-color: #0d6efd; border-radius: 50%; animation: spin 1s linear infinite; mb-3;}
        @keyframes spin { 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>

    <div id="toolbar">
        <div>
            <a href="book_details.php?id=<?php echo $book_id; ?>" class="btn-toolbar text-decoration-none"><i class="fa-solid fa-arrow-left"></i> Library</a>
        </div>
        <div class="d-flex align-items-center">
            <button class="btn-toolbar shadow-sm" id="prev-page"><i class="fa-solid fa-chevron-left me-1"></i> Prev</button>
            <span class="mx-3 text-muted">Page <input type="number" id="page-num-input" value="<?php echo $last_page; ?>"> of <span id="page-count" class="text-white fw-bold">...</span></span>
            <button class="btn-toolbar shadow-sm" id="next-page">Next <i class="fa-solid fa-chevron-right ms-1"></i></button>
        </div>
        <div>
            <span class="fw-bold d-none d-md-inline text-truncate text-secondary" style="max-width: 250px;"><i class="fa-solid fa-book-open me-2"></i><?php echo htmlspecialchars($book['title']); ?></span>
        </div>
    </div>

    <div id="book-container">
        <div id="loader">
            <div class="spinner mb-3"></div>
            <h5 class="text-muted">Loading Book...</h5>
        </div>
        
        <div class="book-wrapper" id="book-wrapper" style="opacity: 0;">
            <!-- Single page reading mode for simplicity with 3D flip effect -->
            <div class="page" id="current-page">
                <canvas id="pdf-canvas"></canvas>
            </div>
        </div>
    </div>

    <script>
        const url = <?php echo json_encode($pdf_url); ?>;
        const bookId = <?php echo $book_id; ?>;
        
        let pdfDoc = null,
            pageNum = <?php echo $last_page; ?>,
            pageRendering = false,
            pageNumPending = null,
            canvas = document.getElementById('pdf-canvas'),
            ctx = canvas.getContext('2d'),
            pageElem = document.getElementById('current-page'),
            bookWrapper = document.getElementById('book-wrapper'),
            loader = document.getElementById('loader');

        // Setup PDF.js version 3.11
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        // Fetch PDF using the modern task API
        const loadingTask = pdfjsLib.getDocument(url);
        
        loadingTask.promise.then(function(pdfDoc_) {
            pdfDoc = pdfDoc_;
            document.getElementById('page-count').textContent = pdfDoc.numPages;
            
            if(pageNum > pdfDoc.numPages) pageNum = pdfDoc.numPages;
            if(pageNum < 1) pageNum = 1;
            
            // Fade out loader, fade in book
            loader.style.display = 'none';
            bookWrapper.style.opacity = '1';
            
            renderPage(pageNum);
        }).catch(function(err) {
            console.error('Error loading PDF: ', err);
            loader.innerHTML = `
                <div class="text-danger mt-5 text-center text-break bg-dark p-4 rounded shadow">
                    <i class="fa-solid fa-triangle-exclamation fa-3x mb-3"></i>
                    <h5>Error Opening Book</h5>
                    <p class="text-muted small mt-2"><strong>Attempted URL:</strong> ${url}</p>
                    <p class="text-danger small mt-2"><strong>JS Error:</strong> ${err.message || err}</p>
                </div>`;
        });

        // Async Page Render
        async function renderPage(num) {
            pageRendering = true;
            
            try {
                const page = await pdfDoc.getPage(num);
                
                // Calculate scale to fit nicely in 85vh container
                const containerHeight = bookWrapper.clientHeight;
                const unscaledViewport = page.getViewport({ scale: 1.0 });
                const desiredScale = containerHeight / unscaledViewport.height;
                const scale = desiredScale * (window.devicePixelRatio || 1) * 1.5; // High-DPI sharpness multiplier
                
                const viewport = page.getViewport({ scale: scale });
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                
                // Maintain CSS size purely to container
                canvas.style.height = '100%';
                canvas.style.width = 'auto';

                const renderContext = {
                    canvasContext: ctx,
                    viewport: viewport
                };
                
                await page.render(renderContext).promise;
                
                pageRendering = false;
                if (pageNumPending !== null) {
                    renderPage(pageNumPending);
                    pageNumPending = null;
                }
            } catch(e) {
                console.error("Page render error", e);
                pageRendering = false;
            }

            document.getElementById('page-num-input').value = num;
            saveProgress(num);
            
            // Re-enable buttons
            document.getElementById('prev-page').disabled = (num <= 1);
            document.getElementById('next-page').disabled = (num >= pdfDoc.numPages);
        }

        function queueRenderPage(num, direction) {
            if (pageRendering) {
                pageNumPending = num;
                return;
            }
            
            // BookNest Signature Flip Animation Sequence
            if (direction === 'next') {
                pageElem.style.transformOrigin = 'left center';
                pageElem.style.transform = 'rotateY(-90deg) scale(0.95)';
                pageElem.style.opacity = '0.5';
            } else if (direction === 'prev') {
                pageElem.style.transformOrigin = 'right center';
                pageElem.style.transform = 'rotateY(90deg) scale(0.95)';
                pageElem.style.opacity = '0.5';
            }
            
            setTimeout(() => {
                // Instantly swap the canvas content while it is "edge on" (90deg)
                renderPage(num).then(() => {
                    // Snap to opposite edge for incoming animation
                    if (direction === 'next') {
                        pageElem.style.transition = 'none';
                        pageElem.style.transform = 'rotateY(90deg) scale(0.95)';
                        
                        // Force reflow
                        void pageElem.offsetWidth;
                        
                        // Swing in
                        pageElem.style.transition = 'transform 0.5s cubic-bezier(0.2, 0.8, 0.2, 1), opacity 0.5s';
                        pageElem.style.transform = 'rotateY(0deg) scale(1)';
                        pageElem.style.opacity = '1';
                    } else if (direction === 'prev') {
                        pageElem.style.transition = 'none';
                        pageElem.style.transform = 'rotateY(-90deg) scale(0.95)';
                        
                        void pageElem.offsetWidth;
                        
                        pageElem.style.transition = 'transform 0.5s cubic-bezier(0.2, 0.8, 0.2, 1), opacity 0.5s';
                        pageElem.style.transform = 'rotateY(0deg) scale(1)';
                        pageElem.style.opacity = '1';
                    }
                });
            }, 250); // half of the CSS transition time
        }

        // Previous Page
        document.getElementById('prev-page').addEventListener('click', () => {
            if (pageNum <= 1) return;
            pageNum--;
            queueRenderPage(pageNum, 'prev');
        });

        // Next Page
        document.getElementById('next-page').addEventListener('click', () => {
            if (pageNum >= pdfDoc.numPages) return;
            pageNum++;
            queueRenderPage(pageNum, 'next');
        });
        
        // Page Input Jump (No transition for distant jumps, just flash load)
        document.getElementById('page-num-input').addEventListener('change', (e) => {
            let num = parseInt(e.target.value);
            if(num >= 1 && num <= pdfDoc.numPages) {
                pageElem.style.opacity = '0';
                setTimeout(() => {
                    pageNum = num;
                    renderPage(num);
                    pageElem.style.opacity = '1';
                }, 200);
            } else {
                e.target.value = pageNum;
            }
        });

        // AJAX Save Progress
        function saveProgress(page) {
            fetch('ajax_save_progress.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `book_id=${bookId}&last_page=${page}`
            }).catch(e => console.log('Read progress unlogged'));
        }
    </script>
</body>
</html>
