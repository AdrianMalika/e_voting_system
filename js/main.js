// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const passwordField = form.querySelector('input[type="password"]');
            const confirmPasswordField = form.querySelector('input[name="confirm_password"]');
            
            if (passwordField && confirmPasswordField) {
                if (passwordField.value.length < 8) {
                    e.preventDefault();
                    alert('Password must be at least 8 characters long');
                    return;
                }
                
                if (passwordField.value !== confirmPasswordField.value) {
                    e.preventDefault();
                    alert('Passwords do not match');
                    return;
                }
            }
        });
    });
});

// Auto-hide messages after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const messages = document.querySelectorAll('.error, .success');
    messages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => message.remove(), 300);
        }, 5000);
    });
});

// Confirm actions
function confirmAction(message) {
    return confirm(message);
}

// Real-time form validation
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('input');
    
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            validateInput(input);
        });
    });
});

function validateInput(input) {
    const value = input.value.trim();
    
    switch(input.type) {
        case 'email':
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                input.setCustomValidity('Please enter a valid email address');
            } else {
                input.setCustomValidity('');
            }
            break;
            
        case 'password':
            if (value.length < 8) {
                input.setCustomValidity('Password must be at least 8 characters long');
            } else {
                input.setCustomValidity('');
            }
            break;
    }
}

// AJAX vote submission
function submitVote(electionId, candidateId) {
    if (!confirmAction('Are you sure you want to cast this vote? This action cannot be undone.')) {
        return;
    }
    
    fetch('submit_vote.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            election_id: electionId,
            candidate_id: candidateId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Vote cast successfully!');
            window.location.reload();
        } else {
            alert(data.message || 'Error casting vote');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error casting vote');
    });
}
