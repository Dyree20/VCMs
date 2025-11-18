document.addEventListener("DOMContentLoaded", () => {
    const takePhotoBtn = document.getElementById('takePhotoBtn');
    const fileUploadBtn = document.getElementById('fileUploadBtn');
    const photoInput = document.getElementById('photo');
    const preview = document.getElementById('preview');
    const form = document.getElementById('clampingForm');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    photoInput.style.display = "none";

    // Check device type and camera support
    const isMobileDevice = () => {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    };

    const supportsUserMedia = () => {
        return !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
    };

    let stream = null;
    let cameraActive = false;

    // File upload button
    if (fileUploadBtn) {
        fileUploadBtn.addEventListener('click', () => {
            photoInput.click();
        });
    }

    // Handle camera button click
    takePhotoBtn.addEventListener('click', async () => {
        if (isMobileDevice()) {
            // On mobile, use native file picker
            photoInput.click();
        } else if (supportsUserMedia()) {
            // On desktop, open camera if available
            await openDesktopCamera();
        } else {
            // Fallback to file picker
            photoInput.click();
        }
    });

    // Desktop camera functionality
    async function openDesktopCamera() {
        try {
            if (!cameraActive) {
                // Request camera access
                if (!stream) {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: { 
                            facingMode: 'environment',
                            width: { ideal: 1280 },
                            height: { ideal: 720 }
                        },
                        audio: false
                    });
                }

                const videoElement = document.getElementById('cameraVideo');
                videoElement.srcObject = stream;
                videoElement.style.display = 'block';

                // Show camera controls
                const controls = document.querySelector('.camera-controls');
                controls.classList.add('active');

                // Add event listeners to controls
                const captureBtn = controls.querySelector('.capture-btn');
                const stopBtn = controls.querySelector('.stop-btn');

                captureBtn.onclick = (e) => {
                    e.preventDefault();
                    captureDesktopPhoto();
                };

                stopBtn.onclick = (e) => {
                    e.preventDefault();
                    closeDesktopCamera();
                };

                takePhotoBtn.textContent = '📷 Camera Active';
                takePhotoBtn.disabled = true;
                cameraActive = true;
            }
        } catch (error) {
            console.error('Camera error:', error);
            if (error.name === 'NotAllowedError') {
                showPopup('Camera access was denied. Please use file upload instead.');
            } else if (error.name === 'NotFoundError') {
                showPopup('No camera device found on this computer.');
            } else {
                showPopup('Camera error: ' + error.message);
            }
            photoInput.click();
        }
    }

    function captureDesktopPhoto() {
        try {
            const videoElement = document.getElementById('cameraVideo');
            if (!videoElement || !videoElement.srcObject) return;

            // Create canvas and capture frame
            const canvas = document.createElement('canvas');
            canvas.width = videoElement.videoWidth;
            canvas.height = videoElement.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(videoElement, 0, 0);

            // Convert to blob and create file
            canvas.toBlob((blob) => {
                const file = new File([blob], `photo_${Date.now()}.png`, { type: 'image/png' });
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                photoInput.files = dataTransfer.files;

                // Show preview
                const reader = new FileReader();
                reader.onload = (e) => {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);

                // Close camera
                closeDesktopCamera();
                showPopup('✅ Photo captured successfully!');
            }, 'image/png');
        } catch (error) {
            console.error('Capture error:', error);
            showPopup('Failed to capture photo.');
        }
    }

    function closeDesktopCamera() {
        const videoElement = document.getElementById('cameraVideo');
        const controls = document.querySelector('.camera-controls');

        // Stop all tracks
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }

        if (videoElement) {
            videoElement.style.display = 'none';
            videoElement.srcObject = null;
        }

        if (controls) {
            controls.classList.remove('active');
        }

        takePhotoBtn.innerHTML = '<i class="fa-solid fa-camera"></i> Take Photo';
        takePhotoBtn.disabled = false;
        cameraActive = false;
    }

    // Handle file input change
    photoInput.addEventListener('change', (event) => {
        closeDesktopCamera();
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
        }
    });

    // Popup helper
    function showPopup(message, id = null) {
        const popup = document.getElementById("successPopup");
        const popupMessage = document.getElementById("popupMessage");
        popupMessage.innerHTML = `
            <i class="fa-solid fa-circle-check"></i>
            <p>${message}</p>
            ${id ? `
                <div style="display: flex; gap: 12px; margin-top: 24px; flex-direction: column; align-items: center;">
                    <button id="printReceiptBtn" style="padding: 12px 24px; background: #007bff; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 14px; width: 100%; max-width: 300px;">
                        <i class="fa-solid fa-print"></i> Print Receipt
                    </button>
                    <button id="viewDashboardBtn" style="padding: 12px 24px; background: #28a745; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 14px; width: 100%; max-width: 300px;">
                        <i class="fa-solid fa-eye"></i> View in Dashboard
                    </button>
                </div>
            ` : ""}
        `;
        popup.style.display = "flex";

        if (id) {
            // Remove any existing listeners
            const oldPrintBtn = document.getElementById("printReceiptBtn");
            const oldViewBtn = document.getElementById("viewDashboardBtn");
            if (oldPrintBtn) {
                oldPrintBtn.replaceWith(oldPrintBtn.cloneNode(true));
            }
            if (oldViewBtn) {
                oldViewBtn.replaceWith(oldViewBtn.cloneNode(true));
            }
            
            // Print button handler
            setTimeout(() => {
                const printBtn = document.getElementById("printReceiptBtn");
                const viewBtn = document.getElementById("viewDashboardBtn");
                
                if (printBtn) {
                    printBtn.addEventListener("click", () => {
                        window.open(`/clampings/receipt/${id}`, "_blank");
                    });
                }
                
                if (viewBtn) {
                    viewBtn.addEventListener("click", () => {
                        window.location.href = "/enforcer/dashboard";
                    });
                }
            }, 100);
        } else {
            // Auto close popup and redirect if no print button
            setTimeout(() => {
                popup.style.display = "none";
                window.location.href = "/enforcer/dashboard";
            }, 4000);
        }
    }

    // Form submission
    let isSubmitting = false; // Prevent double submission
    
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        e.stopPropagation();

        // Prevent double submission
        if (isSubmitting) {
            return;
        }

        isSubmitting = true;
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = 'Adding...';
        }

        const formData = new FormData(form);

        try {
            const response = await fetch(window.clampingsRoute, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                body: formData,
                credentials: 'include'
            });

            let result;
            try {
                result = await response.json();
            } catch {
                result = { success: response.ok, message: "Clamping added successfully!" };
            }

            if (result.success) {
                // Reset form to prevent duplicate submission
                form.reset();
                preview.style.display = 'none';
                
                // Show success popup with print receipt option
                showPopup(result.message || "✅ Clamping added successfully!", result.id);
            } else {
                showPopup(result.message || "Failed to add clamping record.");
                isSubmitting = false;
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = 'ADD CLAMPING';
                }
            }

        } catch (error) {
            console.error(error);
            showPopup("An error occurred while submitting the form.");
            isSubmitting = false;
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = 'ADD CLAMPING';
            }
        }
    });
});
