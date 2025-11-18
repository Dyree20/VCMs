@extends('dashboards.enforcer')

@section('content')
<section class="profile-section">
    <div class="profile-pic-container">
        <img id="profileImage" src="{{ $user->details && $user->details->photo ? asset('storage/' . $user->details->photo) : asset('images/default-avatar.png') }}" alt="Profile">
        <button class="change-photo-btn" id="changePhotoBtn">+</button>
        <input type="file" id="photoInput" accept="image/*" style="display: none;">
    </div>
    <h2>{{ $user->f_name }} {{ $user->l_name }}</h2>
    <p>{{ $user->email }}</p>
</section>

<section class="info-box">
    <div class="info-item"><span>First Name</span><span>{{ $user->f_name }}</span></div>
    <div class="info-item"><span>Last Name</span><span>{{ $user->l_name }}</span></div>
    <div class="info-item"><span>Enforcer ID</span><span>{{ $user->enforcer_id ?? 'N/A' }}</span></div>
    <div class="info-item"><span>Username</span><span>{{ $user->username }}</span></div>
    <div class="info-item"><span>Email</span><span>{{ $user->email }}</span></div>
    <div class="info-item"><span>Role</span><span>{{ $user->role->name ?? 'N/A' }}</span></div>
    <div class="info-item"><span>Address</span><span>{{ $user->details && $user->details->address ? $user->details->address : 'Not specified' }}</span></div>
    <div class="info-item"><span>Gender</span><span>{{ $user->details && $user->details->gender ? $user->details->gender : 'Not specified' }}</span></div>
    <div class="info-item"><span>Birth Date</span><span>{{ $user->details && $user->details->birthdate ? $user->details->birthdate->format('F d, Y') : 'Not specified' }}</span></div>
</section>

<section class="options">
    <div class="option-item" onclick="window.location.href='{{ route('profile.edit') }}'">
        <div class="option-content">
            <span class="option-text">Edit Profile</span>
            <i class="fa-solid fa-chevron-right option-icon"></i>
        </div>
    </div>
    <div class="option-item" onclick="window.location.href='{{ route('transactions.history') }}'">
        <div class="option-content">
            <span class="option-text">Transactions History</span>
            <i class="fa-solid fa-chevron-right option-icon"></i>
        </div>
    </div>
    <div class="option-item" onclick="window.location.href='{{ route('notification.settings') }}'">
        <div class="option-content">
            <span class="option-text">Notification Settings</span>
            <i class="fa-solid fa-chevron-right option-icon"></i>
        </div>
    </div>
    <div class="option-item" onclick="window.location.href='{{ route('contact.us') }}'">
        <div class="option-content">
            <span class="option-text">Contact Us</span>
            <i class="fa-solid fa-chevron-right option-icon"></i>
        </div>
    </div>
    <div class="option-item" onclick="window.location.href='{{ route('help.faqs') }}'">
        <div class="option-content">
            <span class="option-text">Help & FAQs</span>
            <i class="fa-solid fa-chevron-right option-icon"></i>
        </div>
    </div>
</section>

<button type="button" class="auth-button logout logout-btn">Log Out</button>

<style>
    .option-item {
        background: white;
        border-radius: 12px;
        margin-bottom: 12px;
        padding: 0;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        border: 1px solid #f0f0f0;
    }

    .option-item:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        border-color: #2b58ff;
    }

    .option-item:active {
        transform: translateX(3px);
    }

    .option-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 18px;
    }

    .option-text {
        font-size: 15px;
        font-weight: 500;
        color: #333;
        text-decoration: none;
    }

    .option-icon {
        font-size: 14px;
        color: #999;
        transition: all 0.3s ease;
    }

    .option-item:hover .option-icon {
        color: #2b58ff;
        transform: translateX(3px);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const changePhotoBtn = document.getElementById('changePhotoBtn');
    const photoInput = document.getElementById('photoInput');
    const profileImage = document.getElementById('profileImage');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Open file picker when button is clicked
    changePhotoBtn.addEventListener('click', function() {
        photoInput.click();
    });

    // Handle photo selection and upload
    photoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        // Validate file type
        if (!file.type.startsWith('image/')) {
            alert('Please select an image file');
            return;
        }

        // Validate file size (2MB)
        if (file.size > 2048 * 1024) {
            alert('Image size must be less than 2MB');
            return;
        }

        // Show preview immediately
        const reader = new FileReader();
        reader.onload = function(e) {
            profileImage.src = e.target.result;
        };
        reader.readAsDataURL(file);

        // Upload photo
        const formData = new FormData();
        formData.append('photo', file);
        formData.append('_token', csrfToken);

        // Show loading state
        changePhotoBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
        changePhotoBtn.disabled = true;

        fetch('{{ route("profile.update-photo") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update image source with new URL
                if (data.photo_url) {
                    profileImage.src = data.photo_url;
                    // Update header profile picture if it exists (using global function if available)
                    if (typeof updateHeaderProfilePics === 'function') {
                        updateHeaderProfilePics(data.photo_url);
                    } else {
                        // Fallback: update directly
                        const headerProfilePic = document.getElementById('headerProfilePic');
                        if (headerProfilePic) {
                            headerProfilePic.src = data.photo_url;
                        }
                        document.querySelectorAll('#headerProfilePic').forEach(img => {
                            img.src = data.photo_url;
                        });
                    }
                }
                // Show success message
                const successMsg = document.createElement('div');
                successMsg.style.cssText = 'position: fixed; top: 20px; left: 50%; transform: translateX(-50%); background: #28a745; color: white; padding: 12px 24px; border-radius: 8px; z-index: 10000; box-shadow: 0 4px 12px rgba(0,0,0,0.2);';
                successMsg.textContent = 'Profile photo updated successfully!';
                document.body.appendChild(successMsg);
                setTimeout(() => successMsg.remove(), 3000);
            } else {
                alert(data.message || 'Failed to update photo');
                // Revert image if upload failed
                profileImage.src = '{{ $user->details && $user->details->photo ? asset("storage/" . $user->details->photo) : asset("images/default-avatar.png") }}';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while uploading the photo');
            // Revert image
            profileImage.src = '{{ $user->details && $user->details->photo ? asset("storage/" . $user->details->photo) : asset("images/default-avatar.png") }}';
        })
        .finally(() => {
            changePhotoBtn.innerHTML = '+';
            changePhotoBtn.disabled = false;
            photoInput.value = ''; // Reset input
        });
    });
});
</script>

@endsection
