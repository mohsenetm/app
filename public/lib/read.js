class MarkdownViewer {
    constructor() {
        this.container = document.getElementById('markdown-container');
        this.apiUrl = this.container?.dataset.apiUrl;
        this.currentCardId = null;
        this.intervalId = null;

        if (this.apiUrl) {
            this.loadMarkdown();
        }
    }

    async loadMarkdown() {
        try {
            const requestData = {
                action: 'initial',
                card_id: this.currentCardId
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
                throw new Error(`HTTP error: ${response.status}`);
            }

            const data = await response.json();

            if (data.success && data.markdown) {
                this.renderMarkdown(data.markdown);

                if (data.card_id) {
                    this.currentCardId = data.card_id;
                    this.startIdTracking();
                }
            } else {
                this.showError('Failed to load markdown content');
            }
        } catch (error) {
            this.showError(error.message);
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

            // Apply syntax highlighting
            this.container.querySelectorAll('pre code').forEach((block) => {
                hljs.highlightElement(block);
            });

        } catch (error) {
            console.error('Render error:', error);
            this.showError('Error displaying content');
        }
    }

    setTextDirection(text) {
        const persianRegex = /[\u0600-\u06FF]/;
        const isPersian = persianRegex.test(text);
        this.container.setAttribute('dir', isPersian ? 'rtl' : 'ltr');
    }

    async sendCardId() {
        if (!this.currentCardId) return;

        try {
            const requestData = {
                action: 'initial',
                card_id: this.currentCardId
            };

            const response = await fetch('/api/track-card', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(requestData)
            });

            if (!response.ok) {
                console.warn('Failed to send card ID tracking');
            }
        } catch (error) {
            console.warn('Error sending card ID tracking:', error);
        }
    }

    startIdTracking() {
        // Clear any existing interval
        if (this.intervalId) {
            clearInterval(this.intervalId);
        }

        // Send ID every 30 seconds
        this.intervalId = setInterval(() => {
            this.sendCardId();
        }, 30000);
    }

    showError(message) {
        this.container.innerHTML = `
            <div class="error">
                <h3>⚠️ Error</h3>
                <p>${message}</p>
                <button onclick="window.markdownViewer.loadMarkdown()" style="
                    background: white;
                    color: #e74c3c;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 5px;
                    margin-top: 15px;
                    cursor: pointer;
                ">Retry</button>
            </div>
        `;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    window.markdownViewer = new MarkdownViewer();
});
