import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

(function () {
  'use strict';

  function escapeHtml(value) {
      return String(value)
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#039;');
  }

  function showFeedback() {
      var feedback = window.profileFeedback || {};
      var errors = Array.isArray(feedback.errors) ? feedback.errors.filter(Boolean) : [];

      if (errors.length) {
          var items = errors.map(function (error) {
              return '<li>' + escapeHtml(error) + '</li>';
          }).join('');

          Swal.fire({
              icon: 'error',
              title: 'Please review the form',
              html: '<ul class="text-start space-y-1">' + items + '</ul>',
              confirmButtonText: 'Okay',
          });
          return;
      }

      if (feedback.success) {
          Swal.fire({
              icon: 'success',
              title: 'Saved',
              text: feedback.success,
              timer: 1600,
              showConfirmButton: false,
          });
      }
  }

  let loadFile = function (event) {
      var file = event.target.files && event.target.files[0];
      var output = document.getElementById('profile-img');

      if (!file || !output) {
          return;
      }

      var reader = new FileReader();
      reader.onload = function () {
          if (file.type.match('image.*')) {
              output.src = reader.result;
          } else {
              event.target.value = '';
              Swal.fire({
                  icon: 'error',
                  title: 'Invalid image',
                  text: 'Please select a valid image.',
              });
          }
      };
      reader.readAsDataURL(file);
  };

  function initChoices(selector, options) {
      var element = document.querySelector(selector);
      if (!element) {
          return;
      }

      new Choices(element, options);
  }

  // for profile photo update
  let ProfileChange = document.querySelector('#profile-image');
  if (ProfileChange) {
      if (ProfileChange.dataset.originalSrc === undefined) {
          var currentImage = document.getElementById('profile-img');
          ProfileChange.dataset.originalSrc = currentImage ? currentImage.src : '';
      }
      ProfileChange.addEventListener('change', loadFile);
  }

  var resetPreviewButton = document.querySelector('#reset-profile-preview');
  if (resetPreviewButton) {
      resetPreviewButton.addEventListener('click', function () {
          var image = document.getElementById('profile-img');
          var originalSrc = ProfileChange ? (ProfileChange.dataset.originalSrc || '') : '';

          if (image && originalSrc) {
              image.src = originalSrc;
          }

          if (ProfileChange) {
              ProfileChange.value = '';
          }
      });
  }

   /* Start::Choices JS */
   document.addEventListener("DOMContentLoaded", function () {
      showFeedback();

      initChoices('#language', {
          allowHTML: true,
          removeItemButton: true,
      });

      var genericExamples = document.querySelectorAll("[data-trigger]");
      for (let i = 0; i < genericExamples.length; ++i) {
          var element = genericExamples[i];
          new Choices(element, {
              allowHTML: true,
              placeholderValue: "This is a placeholder set in the config",
              searchPlaceholderValue: "Search",
          });
      }
  });

})();
