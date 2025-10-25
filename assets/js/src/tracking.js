export default (documentId) => ({
    startTime: null,
    documentId: documentId,

    init() {
        this.startTime = Date.now();

        window.addEventListener('beforeunload', () => {
            this.sendView();
        });

        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.sendView();
                this.startTime = null;
            }
            else {
                this.startTime = Date.now();
            }
        });
    },

    async sendView() {
        if (!this.startTime) {
            return;
        }

        const duration = Math.floor((Date.now() - this.startTime) / 1000);

        if (duration < 5) {
            return;
        }

        try {
            await fetch('/wp-json/piattaforma/v1/track-view', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': window.meridiana.nonce,
                },
                body: JSON.stringify({
                    document_id: this.documentId,
                    duration: duration,
                }),
                keepalive: true,
            });
        }
        catch (error) {
            console.error('Tracking error:', error);
        }
    }
});