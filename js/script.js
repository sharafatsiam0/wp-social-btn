/**
 * Social Share Buttons Plugin JavaScript
 */

(function () {
  "use strict";

  // Wait for DOM to be ready
  document.addEventListener("DOMContentLoaded", function () {
    initCopyButton();
  });

  /**
   * Initialize copy link button functionality
   */
  function initCopyButton() {
    const copyButtons = document.querySelectorAll(".ssb-copy");

    copyButtons.forEach((button) => {
      button.addEventListener("click", function (e) {
        e.preventDefault();

        const url = this.getAttribute("data-url");

        // Copy to clipboard
        navigator.clipboard
          .writeText(url)
          .then(function () {
            // Show success message
            showNotification("Link copied to clipboard!");

            // Change button appearance temporarily
            const originalText = button.textContent;
            button.textContent = "✓";
            button.style.backgroundColor = "#48bb78";

            setTimeout(function () {
              button.textContent = originalText;
              button.style.backgroundColor = "";
            }, 2000);
          })
          .catch(function (err) {
            console.error("Failed to copy:", err);
            showNotification("Failed to copy link", "error");
          });
      });
    });
  }

  /**
   * Show notification message
   */
  function showNotification(message, type = "success") {
    const notification = document.createElement("div");
    notification.className = "ssb-notification " + type;
    notification.textContent = message;
    notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: ${type === "error" ? "#f56565" : "#48bb78"};
            color: white;
            padding: 12px 20px;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 9999;
            animation: slideIn 0.3s ease-out;
        `;

    document.body.appendChild(notification);

    // Remove notification after 3 seconds
    setTimeout(function () {
      notification.style.animation = "slideOut 0.3s ease-out";
      setTimeout(function () {
        notification.remove();
      }, 300);
    }, 3000);
  }

  // Add animation styles
  if (!document.getElementById("ssb-animations")) {
    const style = document.createElement("style");
    style.id = "ssb-animations";
    style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(400px);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(400px);
                    opacity: 0;
                }
            }
        `;
    document.head.appendChild(style);
  }
})();
