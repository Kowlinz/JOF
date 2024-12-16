// Initialize Flatpickr
flatpickr("#date", {
    dateFormat: "Y-m-d",
    altInput: true,
    altFormat: "F j, Y",
    minDate: "today",
    disableMobile: true,
    monthSelectorType: "static",
    nextArrow: ">",
    prevArrow: "<",
  });
  
  // Time Slot Dropdown Logic
  document.addEventListener("DOMContentLoaded", () => {
    const dropdownButton = document.querySelector("#time-slot-button");
    const optionsContainer = document.querySelector(".time-slot-options");
    const options = document.querySelectorAll(".time-slot");
  
    // Toggle dropdown visibility
    dropdownButton.addEventListener("click", (e) => {
      e.preventDefault();
      optionsContainer.classList.toggle("show");
    });
  
    // Set the selected time slot
    options.forEach((option) => {
      option.addEventListener("click", () => {
        dropdownButton.textContent = option.textContent;
        optionsContainer.classList.remove("show");
      });
    });
  
    // Close dropdown if clicked outside
    document.addEventListener("click", (e) => {
      if (!e.target.closest(".time-slot-dropdown")) {
        optionsContainer.classList.remove("show");
      }
    });
  });
  
  // Toggle the visibility of the Add-ons list when the "Add-ons" option is selected from the Service dropdown
  document.getElementById("service").addEventListener("change", function () {
    const selectedService = this.value;
    const addonsList = document.getElementById("addonsList");
  
    // Show the add-ons options if "Add-ons" is selected
    if (selectedService === "addons") {
      addonsList.style.display = "block"; // Show the add-ons list
    } else {
      addonsList.style.display = "none"; // Hide the add-ons list
    }
  });
  
  // Toggle the "Upload Haircut Photo" container based on haircut dropdown
  function toggleUploadContainer() {
    const haircutDropdown = document.getElementById("haircut");
    const uploadContainer = document.getElementById("uploadContainer");
    if (haircutDropdown.value === "uploadPhoto") {
      uploadContainer.style.display = "block";
    } else {
      uploadContainer.style.display = "none";
      resetUploadFields();
    }
  }
  
  // Preview the uploaded photo
  function showPreview(event) {
    const input = event.target;
    const preview = document.getElementById("haircutPreview");
    const removeButton = document.getElementById("removeButton");
  
    if (input.files && input.files[0]) {
      const reader = new FileReader();
  
      reader.onload = function (e) {
        preview.src = e.target.result; // Set the src of the image to the file data
        preview.style.display = "block"; // Make the image visible
        removeButton.style.display = "inline-block"; // Show the remove button
      };
  
      reader.readAsDataURL(input.files[0]); // Read the uploaded file
    }
  }

  // Remove the uploaded image
  function removeImage(event) {
    event.preventDefault();
    const input = document.getElementById("haircutPhoto");
    const preview = document.getElementById("haircutPreview");
    const removeButton = document.getElementById("removeButton");
  
    input.value = ""; // Clear the input value
    preview.src = ""; // Clear the image source
    preview.style.display = "none"; // Hide the image
    removeButton.style.display = "none"; // Hide the remove button
  }
  
  // Reset upload fields
  function resetUploadFields() {
    const preview = document.getElementById("haircutPreview");
    const removeButton = document.getElementById("removeButton");
    const fileInput = document.getElementById("haircutPhoto");
    preview.src = "";
    preview.style.display = "none";
    removeButton.style.display = "none";
    fileInput.value = "";
  }
  
  // Add event listener for haircut dropdown change
  document.getElementById("haircut").addEventListener("change", toggleUploadContainer);

  document.querySelectorAll('.time-slot').forEach(slot => {
    slot.addEventListener('click', () => {
        document.getElementById('time-slot-button').innerText = slot.innerText;
        document.querySelector('[name="timeSlot"]').value = slot.innerText;
    });
});

    // JavaScript to handle time slot selection
    const timeSlotButton = document.getElementById('time-slot-button');
    const timeSlotOptions = document.querySelector('.time-slot-options');
    const selectedTimeSlotInput = document.getElementById('selectedTimeSlot');

    // Show or hide time slot options
    timeSlotButton.addEventListener('click', () => {
        timeSlotOptions.style.display = timeSlotOptions.style.display === 'none' ? 'block' : 'none';
    });

    // Set the selected time slot
    function selectTimeSlot(time) {
        selectedTimeSlotInput.value = time; // Set the hidden input's value
        timeSlotButton.textContent = time; // Update the button text
        timeSlotOptions.style.display = 'none'; // Hide the options
    }

    // Close the options if clicked outside
    document.addEventListener('click', (e) => {
        if (!timeSlotOptions.contains(e.target) && e.target !== timeSlotButton) {
            timeSlotOptions.style.display = 'none';
        }
    });

    document.getElementById('date').addEventListener('change', function() {
      let selectedDate = this.value;
      fetchAvailableSlots(selectedDate);
  });
  
  function fetchAvailableSlots(date) {
      // Send the selected date to the server to check available slots
      fetch('check_time_slots.php?date=' + date)
          .then(response => response.json())
          .then(data => {
              let timeSlots = document.querySelectorAll('.time-slot');
              timeSlots.forEach(slot => {
                  let time = slot.textContent;
                  if (data.includes(time)) {
                      slot.classList.add('booked');
                      slot.disabled = true;
                  } else {
                      slot.classList.remove('booked');
                      slot.disabled = false;
                  }
              });
          });
  }

  