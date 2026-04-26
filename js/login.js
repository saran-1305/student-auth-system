$(document).ready(function() {
    // Check if user is already logged in
    var token = localStorage.getItem('session_token');
    if(token) {
        window.location.href = 'profile.html';
    }

    $("#loginBtn").click(function(e) {
        e.preventDefault();
        var email = $("#email").val().trim();
        var password = $("#password").val().trim();

        if(email === '' || password === '') {
            $("#error-msg").text("Email and password are required").show();
            return;
        }

        $.ajax({
            url: "php/login.php",
            type: "POST",
            dataType: "json",
            data: {
                email: email,
                password: password
            },
            success: function(response) {
                // response is automatically parsed as JSON
                if(response.status === 'success') {
                    // store details in local storage like asked
                    localStorage.setItem('user_id', response.user_id);
                    localStorage.setItem('session_token', response.session_token);
                    
                    // Redirect to profile
                    window.location.href = 'profile.html';
                } else {
                    $("#error-msg").text(response.message).show();
                }
            },
            error: function(xhr, status, error) {
                // If it's a server/PHP error, attempt to extract clean text
                var errorMsg = "Unexpected error please try again later.";
                if(xhr.responseText && xhr.responseText.indexOf('<b>Fatal error</b>') !== -1) {
                    errorMsg = "Backend Error: Check if Redis extension is installed in PHP.";
                }
                $("#error-msg").text(errorMsg).show();
            }
        });
    });
});
