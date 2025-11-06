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

        // Use REST URL from localized script (works across all environments)
        const restUrl = window.meridiana?.resturl || '/wp-json/piattaforma/v1/';
        const endpoint = restUrl + 'track-view';

        try {
            await fetch(endpoint, {
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