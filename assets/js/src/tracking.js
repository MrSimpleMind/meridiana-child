export default (documentId) => ({
    startTime: null,
    documentId: documentId,
    documentType: null,

    init() {
        this.startTime = Date.now();

        // Ricava il document_type dal body o dal post_type del WordPress
        this.documentType = document.body.getAttribute('data-post-type') ||
                           (window.meridiana && window.meridiana.postType) || 'unknown';

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
                    document_type: this.documentType,
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