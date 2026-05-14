$(function () {
  const $form = $('#loginForm');
  const $alert = $('#loginAlert');

  function showAlert(type, message) {
    $alert.removeClass('d-none alert-success alert-danger').addClass(`alert-${type}`).text(message);
  }

  $form.on('submit', function (event) {
    event.preventDefault();

    const payload = {
      identifier: $('#identifier').val().trim(),
      password: $('#loginPassword').val()
    };

    if (!payload.identifier || !payload.password) {
      showAlert('danger', 'Username/email and password are required.');
      return;
    }

    $.ajax({
      url: 'php/login.php',
      method: 'POST',
      dataType: 'json',
      data: payload,
      success(response) {
        if (!response.success) {
          showAlert('danger', response.message || 'Login failed.');
          return;
        }

        localStorage.setItem('session_token', response.token);
        window.location.href = 'profile.html';
      },
      error() {
        showAlert('danger', 'Unable to reach the login endpoint.');
      }
    });
  });
});