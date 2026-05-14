$(function () {
  const token = localStorage.getItem('session_token');

  if (!token) {
    window.location.href = 'login.html';
    return;
  }

  const $alert = $('#profileAlert');

  function showAlert(type, message) {
    $alert.removeClass('d-none alert-success alert-danger').addClass(`alert-${type}`).text(message);
  }

  function loadProfile() {
    $.ajax({
      url: 'php/profile.php',
      method: 'GET',
      dataType: 'json',
      data: { action: 'get', token },
      success(response) {
        if (!response.success) {
          showAlert('danger', response.message || 'Unable to load profile.');
          return;
        }

        const profile = response.data;
        $('#summaryName').text(profile.full_name || '-');
        $('#summaryUsername').text(profile.username || '-');
        $('#summaryEmail').text(profile.email || '-');

        $('#age').val(profile.age || '');
        $('#dob').val(profile.dob || '');
        $('#contact').val(profile.contact || '');
        $('#city').val(profile.city || '');
        $('#address').val(profile.address || '');
        $('#bio').val(profile.bio || '');
      },
      error() {
        showAlert('danger', 'Unable to reach the profile endpoint.');
      }
    });
  }

  $('#profileForm').on('submit', function (event) {
    event.preventDefault();

    const payload = {
      action: 'update',
      token,
      age: $('#age').val().trim(),
      dob: $('#dob').val(),
      contact: $('#contact').val().trim(),
      city: $('#city').val().trim(),
      address: $('#address').val().trim(),
      bio: $('#bio').val().trim()
    };

    $.ajax({
      url: 'php/profile.php',
      method: 'POST',
      dataType: 'json',
      data: payload,
      success(response) {
        if (response.success) {
          showAlert('success', response.message || 'Profile updated.');
          return;
        }

        showAlert('danger', response.message || 'Profile update failed.');
      },
      error() {
        showAlert('danger', 'Unable to save profile changes.');
      }
    });
  });

  $('#logoutBtn').on('click', function () {
    $.ajax({
      url: 'php/profile.php',
      method: 'POST',
      dataType: 'json',
      data: { action: 'logout', token },
      complete() {
        localStorage.removeItem('session_token');
        window.location.href = 'login.html';
      }
    });
  });

  loadProfile();
});