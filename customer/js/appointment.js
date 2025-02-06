// Function to handle appointment cancellation and populate modal
function openCancelModal(appointmentID, date, time, serviceName, addonName) {
    document.getElementById('appointmentDate').textContent = date;
    document.getElementById('appointmentTime').textContent = time;
    document.getElementById('serviceName').textContent = serviceName ? serviceName : 'No service selected';
    document.getElementById('addonName').textContent = addonName ? addonName : 'No add-on selected';

    // Set up the Confirm button to handle the cancellation
    const confirmButton = document.getElementById('confirmCancelButton');
    confirmButton.onclick = function () {
        const reasonInput = document.getElementById('cancelReason');
        const reason = reasonInput.value.trim();

        if (reason === "") {
            alert("Please provide a reason for cancellation.");
            reasonInput.focus(); // Focus on input if empty
            return;
        }

        // Redirect to cancellation PHP script with parameters
        window.location.href = "cancel_appointment.php?appointmentID=" + appointmentID + "&reason=" + encodeURIComponent(reason);
    };
}

// JavaScript to toggle dropdown visibility
const dropdownToggle = document.getElementById('userDropdown');
const dropdownMenu = document.getElementById('dropdownMenu');

dropdownToggle.addEventListener('click', function () {
    dropdownMenu.classList.toggle('show');
});

// Close dropdown when clicking outside
document.addEventListener('click', function (event) {
    if (!dropdownToggle.contains(event.target) && !dropdownMenu.contains(event.target)) {
        dropdownMenu.classList.remove('show');
    }
});

// JavaScript to toggle mobile menu
const menuBtn = document.getElementById('menuBtn');
const menuDropdown = document.getElementById('menuDropdown');
const menuClose = document.getElementById('menuClose');

menuBtn.addEventListener('click', function () {
    menuDropdown.classList.toggle('show');
});

menuClose.addEventListener('click', function () {
    menuDropdown.classList.remove('show');
});

// Close menu when clicking outside
document.addEventListener('click', function (event) {
    if (!menuBtn.contains(event.target) && !menuDropdown.contains(event.target)) {
        menuDropdown.classList.remove('show');
    }
}); 