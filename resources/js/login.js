function toggleCaptcha() {
    const captchaContainer = document.getElementById('captcha-container');
    if (captchaContainer) {
        captchaContainer.style.display = 'block';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    toggleCaptcha();
});
