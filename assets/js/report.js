document.getElementById("reportAreaBtn").addEventListener("click", function () {
    showForm("area");
});

document.getElementById("reportBowserBtn").addEventListener("click", function () {
    showForm("bowser");
});


document.addEventListener('DOMContentLoaded', function() {
    const reportForm = document.querySelector('.report-form form');
    if (reportForm) {
        reportForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Disable submit button and show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';
            
            // Submit form
            fetch(this.action, {
                method: 'POST',
                body: new FormData(this)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                window.location.reload();
            })
            .catch(error => {
                // Show error message
                const feedback = document.createElement('div');
                feedback.className = 'feedback-message feedback-error';
                feedback.innerHTML = 'An error occurred. Please try again.' +
                    '<span class="feedback-close" onclick="this.parentElement.remove()">×</span>';
                this.insertBefore(feedback, this.firstChild);
                
                // Reset button
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
        });
    }
    
    // Auto-hide feedback messages after 5 seconds
    const feedbackMessages = document.querySelectorAll('.feedback-message');
    feedbackMessages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => message.remove(), 500);
        }, 5000);
    });
});