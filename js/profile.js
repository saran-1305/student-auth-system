$(document).ready(function() {
    // Check if logged in using localStorage
    var user_id = localStorage.getItem('user_id');
    var token = localStorage.getItem('session_token');

    if(!user_id || !token) {
        // Not logged in, redirect to login
        window.location.href = 'login.html';
    }

    // Display user ID on the UI
    $('#displayUserId').text(user_id);

    // load previous profile data on page load
    $.ajax({
        url: 'php/profile.php',
        type: 'GET',
        data: {
            user_id: user_id,
            session_token: token,
            action: 'get_profile'
        },
        success: function(response) {
            var res = typeof response === 'string' ? JSON.parse(response) : response;
            if(res.status == 'success' && res.data) {
                // fill the form with existing data
                $('#age').val(res.data.age);
                $('#dob').val(res.data.dob);
                $('#contact').val(res.data.contact);
            } else if(res.status == 'error' && res.message == 'Unauthorized') {
                // session might be invalid or expired
                localStorage.clear();
                window.location.href = 'login.html';
            }
        }
    });

    // Handle update profile button
    $('#updateProfileBtn').click(function() {
        var age = $('#age').val();
        var dob = $('#dob').val();
        var contact = $('#contact').val();

        $.ajax({
            url: 'php/profile.php',
            type: 'POST',
            data: {
                user_id: user_id,
                session_token: token,
                action: 'update_profile',
                age: age,
                dob: dob,
                contact: contact
            },
            success: function(response) {
                var res = typeof response === 'string' ? JSON.parse(response) : response;
                var msgDiv = $('#message');
                if(res.status == 'success') {
                    msgDiv.removeClass('alert-danger').addClass('alert-success');
                    msgDiv.text('Profile updated successfully!').show();
                } else {
                    msgDiv.removeClass('alert-success').addClass('alert-danger');
                    msgDiv.text(res.message).show();
                }
            },
            error: function(xhr, status, error) {
                var msgDiv = $('#message');
                msgDiv.removeClass('alert-success').addClass('alert-danger');
                msgDiv.text('AJAX Error: ' + error + ' | Response: ' + xhr.responseText).show();
            }
        });
    });

    // Handle logout
    $('#logoutBtn').click(function() {
        $.ajax({
            url: 'php/login.php',
            type: 'POST',
            data: {
                action: 'logout',
                session_token: token
            },
            success: function() {
                // always clear local storage on logout
                localStorage.clear();
                window.location.href = 'login.html';
            }
        });
    });
});
