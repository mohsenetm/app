class MarkdownViewer {
    constructor() {
        this.initializeElements();
        this.initializeState();
        this.setupMarked();
        this.setupKeyboardListeners();
        this.loadContent();
    }

    initializeElements() {
        this.container = document.getElementById('markdown-container');
        this.remainingCardsElement = document.getElementById('remaining-cards');
        this.againElement = document.getElementById('again');
        this.hardElement = document.getElementById('hard');
        this.goodElement = document.getElementById('good');
        this.easyElement = document.getElementById('easy');
    }

    initializeState() {
        this.isLoading = false;
        this.apiUrl = this.container.dataset.apiUrl;
        this.name = null;
        this.currentSide = 'front';
        this.cardData = null;
    }

    setupMarked() {
        marked.setOptions({
            highlight: function(code, lang) {
                if (lang && hljs.getLanguage(lang)) {
                    try {
                        return hljs.highlight(code, { language: lang }).value;
                    } catch (err) {
                        console.warn('خطا در syntax highlighting:', err);
                    }
                }
                return hljs.highlightAuto(code).value;
            },
            langPrefix: 'hljs language-',
            breaks: true,
            gfm: true,
            tables: true,
            sanitize: false
        });
    }

    async loadContent(action = 'initial') {
        if (this.isLoading) return;

        this.isLoading = true;
        this.showLoading();

        try {
            const requestData = {
                action: action,
                card_id: this.name
            };

            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(requestData)
            });

            if (!response.ok) {
                throw new Error(`خطای HTTP: ${response.status}`);
            }

            const data = await response.json();

            if (data.success) {
                // Store card data with front and back
                this.cardData = {
                    front: data.front || data.markdown,
                    back: data.back
                };

                // Reset to front side for new card
                this.currentSide = 'front';

                // Render the current side
                this.renderMarkdown(this.cardData[this.currentSide]);

                // Update current card ID if provided
                if (data.card_id !== undefined) {
                    this.name = data.card_id;
                }
                this.updateStats(data);
            } else {
                throw new Error(data.message || 'خطا در دریافت محتوا');
            }

        } catch (error) {
            this.showError(error.message);
        } finally {
            this.isLoading = false;
        }
    }

    renderMarkdown(markdownText) {
        try {
            const htmlContent = marked.parse(markdownText);
            this.container.innerHTML = `
                <div class="markdown-content">
                    ${htmlContent}
                </div>
            `;

            // Set text direction based on language detection
            this.setTextDirection(markdownText);

            // اعمال syntax highlighting
            this.container.querySelectorAll('pre code').forEach((block) => {
                hljs.highlightElement(block);
            });

            // اسکرول به بالا
            this.container.scrollIntoView({ behavior: 'smooth' });

        } catch (error) {
            console.error('خطا در رندر:', error);
            this.showError('خطا در نمایش محتوا');
        }
    }

    setTextDirection(text) {
        // Detect Persian characters (Unicode range: \u0600-\u06FF)
        const persianRegex = /[\u0600-\u06FF]/;
        const isPersian = persianRegex.test(text);

        // Set direction attribute on container
        this.container.setAttribute('dir', isPersian ? 'rtl' : 'ltr');
    }

    showLoading() {
        this.container.innerHTML = '<div class="loading">در حال دریافت محتوا از سرور...</div>';
    }

    showError(message) {
        this.container.innerHTML = `
            <div class="error">
                <h3>⚠️ خطا</h3>
                <p>${message}</p>
                <button onclick="window.markdownViewer.loadContent()" style="
                    background: white;
                    color: #e74c3c;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 5px;
                    margin-top: 15px;
                    cursor: pointer;
                ">تلاش مجدد</button>
            </div>
        `;
    }

    updateElementContent(element, count) {
        if (element) {
            element.textContent = count;
        }
    }

    updateRemainingCardsContent(count) {
        this.updateElementContent(this.remainingCardsElement, count);
    }

    updateHardContent(count) {
        this.updateElementContent(this.hardElement, count);
    }

    updateEasyContent(count) {
        this.updateElementContent(this.easyElement, count);
    }

    updateGoodContent(count) {
        this.updateElementContent(this.goodElement, count);
    }

    updateAgainContent(count) {
        this.updateElementContent(this.againElement, count);
    }

    setupKeyboardListeners() {
        document.addEventListener('keydown', (event) => {
            // Handle Enter key to toggle card sides
            if (event.key === 'Enter') {
                event.preventDefault();
                this.toggleSide();
            }

            // Handle number keys 1-4 for rating options
            if (event.key >= '1' && event.key <= '4') {
                event.preventDefault();
                const actions = ['again', 'hard', 'good', 'easy'];
                const action = actions[parseInt(event.key) - 1];
                this.loadContent(action);
            }
        });
    }

    toggleSide() {
        if (!this.cardData) return;
        this.currentSide = this.currentSide === 'front' ? 'back' : 'front';
        this.renderMarkdown(this.cardData[this.currentSide]);
    }

    updateStats(data) {
        const stats = ['remainingCards', 'hard', 'good', 'easy', 'again'];
        stats.forEach(stat => {
            if (data[stat] !== undefined) {
                const methodName = `update${stat.charAt(0).toUpperCase() + stat.slice(1)}Content`;
                if (typeof this[methodName] === 'function') {
                    this[methodName](data[stat]);
                }
            }
        });
    }
}

async function performAction(action) {
    const viewer = window.markdownViewer;
    await viewer.loadContent(action);
}

// راه‌اندازی
document.addEventListener('DOMContentLoaded', function() {
    window.markdownViewer = new MarkdownViewer();
});
