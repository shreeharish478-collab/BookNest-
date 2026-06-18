/**
 * assets/js/script.js
 */

document.addEventListener('DOMContentLoaded', () => {

    // 1. Dark Mode Toggle
    const themeToggleBtn = document.getElementById('theme-toggle');
    const body = document.body;
    
    // Check saved theme
    if(localStorage.getItem('theme') === 'dark') {
        body.classList.add('dark-mode');
        updateThemeIcon(true);
    }

    if(themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            const isDark = body.classList.contains('dark-mode');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            updateThemeIcon(isDark);
        });
    }

    function updateThemeIcon(isDark) {
        if(themeToggleBtn) {
            themeToggleBtn.innerHTML = isDark ? '<i class="fa-solid fa-sun"></i>' : '<i class="fa-solid fa-moon"></i>';
        }
    }

    // 2. Chatbot Haxr AI injected to body
    injectChatbot();

    // 3. Live AJAX Search (Global Search Bar)
    const searchInput = document.getElementById('global-search');
    const searchDropdown = document.getElementById('search-results-dropdown');

    if(searchInput && searchDropdown) {
        let timeout = null;

        searchInput.addEventListener('input', function() {
            clearTimeout(timeout);
            const query = this.value.trim();

            if(query.length < 2) {
                searchDropdown.classList.add('d-none');
                return;
            }

            timeout = setTimeout(() => {
                fetch(`${BASE_URL}/books/ajax_search.php?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        searchDropdown.innerHTML = '';
                        if(data.length > 0) {
                            data.forEach(book => {
                                const a = document.createElement('a');
                                a.href = `${BASE_URL}/books/book_details.php?id=${book.id}`;
                                a.className = 'list-group-item list-group-item-action d-flex align-items-center';
                                // In a real scenario use the actual image path, fallback to a placeholder text or icon
                                a.innerHTML = `
                                    <div class="me-3"><i class="fa-solid fa-book text-muted"></i></div>
                                    <div>
                                        <div class="fw-bold text-dark">${book.title}</div>
                                        <small class="text-muted">${book.author}</small>
                                    </div>
                                `;
                                searchDropdown.appendChild(a);
                            });
                        } else {
                            searchDropdown.innerHTML = '<div class="list-group-item text-muted">No books found</div>';
                        }
                        searchDropdown.classList.remove('d-none');
                    })
                    .catch(err => console.error('Search error:', err));
            }, 300);
        });

        // Hide dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if(!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
                searchDropdown.classList.add('d-none');
            }
        });
    }

});

// --- Chatbot Functionality ---
function injectChatbot() {
    const chatbotHTML = `
        <div id="chatbot-toggle">
            <i class="fa-solid fa-robot"></i>
        </div>
        <div id="chatbot-container">
            <div id="chatbot-header">
                <span><i class="fa-solid fa-robot me-2"></i> Haxr AI</span>
                <button id="chatbot-close" class="btn btn-sm btn-link text-white p-0"><i class="fa-solid fa-times"></i></button>
            </div>
            <div id="chatbot-messages">
                <div class="chat-msg msg-bot">Hi there! I'm Haxr AI. How can I help you? Try asking: "How to read books", "How to save books", or "How to write a review".</div>
            </div>
            <div id="chatbot-input-area">
                <input type="text" id="chatbot-input" class="chat-input" placeholder="Ask something..." autocomplete="off">
                <button id="chatbot-send" class="btn text-primary"><i class="fa-solid fa-paper-plane"></i></button>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', chatbotHTML);

    const toggleBtn = document.getElementById('chatbot-toggle');
    const container = document.getElementById('chatbot-container');
    const closeBtn = document.getElementById('chatbot-close');
    const sendBtn = document.getElementById('chatbot-send');
    const input = document.getElementById('chatbot-input');
    const messages = document.getElementById('chatbot-messages');

    toggleBtn.addEventListener('click', () => {
        container.style.display = 'flex';
        toggleBtn.style.display = 'none';
        input.focus();
    });

    closeBtn.addEventListener('click', () => {
        container.style.display = 'none';
        toggleBtn.style.display = 'flex';
    });

    function sendMessage() {
        const text = input.value.trim();
        if(!text) return;

        // User message
        addMessage(text, 'user');
        input.value = '';

        // Bot response
        setTimeout(() => {
            const response = getBotResponse(text.toLowerCase());
            addMessage(response, 'bot');
        }, 500);
    }

    sendBtn.addEventListener('click', sendMessage);
    input.addEventListener('keypress', (e) => {
        if(e.key === 'Enter') sendMessage();
    });

    function addMessage(text, sender) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `chat-msg msg-${sender}`;
        msgDiv.textContent = text;
        messages.appendChild(msgDiv);
        messages.scrollTop = messages.scrollHeight;
    }

    function getBotResponse(input) {
        if(input.includes('read books') || input.includes('read')) {
            return "To read a book, go to 'Browse' or the Home page, click on a book card, and hit the 'Read Book' button to open the PDF reader and track your progress!";
        } else if (input.includes('save books') || input.includes('library')) {
            return "You can save books by clicking the 'Save to Library' bookmark icon on the book's details page. It will appear in 'My Library'.";
        } else if (input.includes('review') || input.includes('rate')) {
            return "On any book's detail page, scroll down to the reviews section to leave a 1-5 star rating and your thoughts.";
        } else if (input.includes('hello') || input.includes('hi')) {
            return "Hello! Enjoy reading at BookNest. Need help with the platform?";
        } else {
            return "I'm not sure. I can help with reading books, saving to library, and reviews!";
        }
    }
}

// Global functions for reading progress mapping
function saveReadingProgress(bookId, pageNum) {
    fetch(`${BASE_URL}/books/ajax_save_progress.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `book_id=${bookId}&last_page=${pageNum}`
    }).then(res => res.text()).then(data => console.log('Progress saved:', data)).catch(err => console.error(err));
}
