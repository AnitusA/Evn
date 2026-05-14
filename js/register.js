$(function () {
  const $form = $('#registerForm');
  const $alert = $('#registerAlert');

  function showAlert(type, message) {
    $alert.removeClass('d-none alert-success alert-danger').addClass(`alert-${type}`).text(message);
  }

  $form.on('submit', function (event) {
    event.preventDefault();

    const payload = {
      full_name: $('#fullName').val().trim(),
      username: $('#username').val().trim(),
      email: $('#email').val().trim(),
      password: $('#password').val(),
      confirm_password: $('#confirmPassword').val()
    };

    if (!payload.full_name || !payload.username || !payload.email || !payload.password || !payload.confirm_password) {
      showAlert('danger', 'All fields are required.');
      return;
    }

    if (payload.password !== payload.confirm_password) {
      showAlert('danger', 'Passwords do not match.');
      return;
    }

    $.ajax({
      url: 'php/register.php',
      method: 'POST',
      dataType: 'json',
      data: payload,
      success(response) {
        if (response.success) {
          showAlert('success', response.message);
          setTimeout(() => {
            window.location.href = 'login.html';
          }, 900);
          return;
        }

        showAlert('danger', response.message || 'Registration failed.');
      },
      error() {
        showAlert('danger', 'Unable to reach the registration endpoint.');
      }
    });
  });
});