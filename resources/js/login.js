export default function loginHero(images = []) {
    return {
        current: 0,
        now: new Date(),

        images:
            images.length > 0
                ? images
                : [
                      '/images/login/Background 1.png',
                      '/images/login/Background 2.png',
                      '/images/login/Background 3.png',
                      '/images/login/Background 4.png',
                  ],

        init() {
            // console.log('Login Hero Initialized');
            // console.log('Images:', this.images);

            this.updateClock();

            setInterval(() => {
                this.nextImage();
            }, 5000);

            setInterval(() => {
                this.updateClock();
            }, 1000);
        },

        nextImage() {
            this.current = (this.current + 1) % this.images.length;
        },

        updateClock() {
            this.now = new Date();
        },

        get time() {
            return this.now.toLocaleTimeString('en-EN', {
                hour: '2-digit',
                minute: '2-digit',
            });
        },

        get date() {
            return this.now.toLocaleDateString('en-EN', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric',
            });
        },
    };
}

