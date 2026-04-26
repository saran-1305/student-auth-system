$(document).ready(function() {
    // When register button is clicked
    $('#registerBtn').click(function(e) {
        e.preventDefault();
        
        // Get values from inputs
        var name = $('#name').val().trim();
        var email = $('#email').val().trim();
        var password = $('#password').val().trim();

        // Simple validation
        if (name === '' || email === '' || password === '') {
            $('#error-msg').text('Please fill all fields!').show();
            $('#success-msg').hide();
            return; // Stop execution
        }

        // Send AJAX request to PHP
        $.ajax({
            url: 'php/register.php',
            type: 'POST',
            dataType: 'json',
            data: {
                name: name,
                email: email,
                password: password
            },
            success: function(response) {
                // response is already a JSON object because of dataType: 'json' 
                // and PHP sending Content-Type: application/json
                if (response.status === 'success') {
                    $('#success-msg').text(response.message).show();
                    $('#error-msg').hide();
                    // Clear inputs
                    $('#registerForm')[0].reset();
                } else {
                    $('#error-msg').text(response.message).show();
                    $('#success-msg').hide();
                }
            },
            error: function() {
                $('#error-msg').text('Server error occurred.').show();
            }
        });
    });
});
