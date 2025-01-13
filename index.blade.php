@extends('layouts.app')

@section('content')
<style>
    body {
        margin: 0;
        padding: 0;
        overflow: hidden;
        font-family: Arial, sans-serif;
    }

    .video-container {
        height: 80vh;
        width: 100vw;
        display: flex;
        justify-content: center;
        align-items: center;
        overflow: hidden;
        /* Remove scrollbar */
    }

    .video-item {
        height: 100%;
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
    }

    video {
        height: 100%;
        width: auto;
        max-width: 100%;
        object-fit: cover;
    }

    .controls {
        position: absolute;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        justify-content: space-between;
        width: 200px;
        opacity: 0;
        transition: opacity 0.3s;
        z-index: 10;
    }

    .video-item:hover .controls {
        opacity: 1;
        /* Show controls on hover */
    }

    .controls button {
        background-color: rgba(0, 0, 0, 0.5);
        color: white;
        border: none;
        padding: 10px;
        border-radius: 5px;
        cursor: pointer;
    }

    .controls button:hover {
        background-color: rgba(0, 0, 0, 0.7);
    }

    .actions {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 10px;
        z-index: 10;
    }

    .actions button {
        background-color: rgba(0, 0, 0, 0.5);
        color: white;
        border: none;
        padding: 10px;
        border-radius: 5px;
        cursor: pointer;
    }

    .actions button:hover {
        background-color: rgba(0, 0, 0, 0.7);
    }
</style>

<div class="video-container">
    <div class="video-item">
        <video id="currentVideo" loop preload="metadata" playsinline muted>
            <source src="{{ asset($videos[0]->file_path) }}" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <div class="controls">
            <button class="play-pause">⏸️</button>
            <button class="mute-unmute">🔇</button>
        </div>
        <div class="actions">
            <button id="likeButton" data-video-id="{{ $videos[0]->id }}"
                data-liked="{{ $videos[0]->likes_count > 0 ? 'true' : 'false' }}">
                {{ $videos[0]->likes_count > 0 ? '❤️' : '♡' }}
            </button>
            <!-- <span class="likes-count" id="likes-count">{{ $videos[0]->likes_count }} Likes</span>
            <span class="comments-count" id="comments-count">{{ $videos[0]->comments_count }} Comments</span> -->

            @auth
                <!-- Show Comment button if user is logged in -->
                <button id="commentButton" data-bs-toggle="modal" data-bs-target="#commentModal">Comment</button>
            @else
                <!-- Show Login button if user is not logged in -->
                <a href="{{ route('login') }}?redirectTo={{ request()->fullUrl() }}" class="btn btn-primary">Login to
                    Comment</a>
            @endauth
        </div>
    </div>
</div>

<!-- Bootstrap Modal -->
<div class="modal fade" id="commentModal" tabindex="-1" aria-labelledby="commentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="commentModalLabel">Add Comment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="commentForm">
                <div class="modal-body">
                    <textarea name="comment" class="form-control" placeholder="Write your comment..."
                        required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const videos = @json($videos); // Pass videos array from Laravel to JS
        const videoContainer = document.querySelector('.video-container');
        const currentVideoElement = document.getElementById('currentVideo');
        let currentIndex = 0; // Track the currently active video
        let isScrolling = false; // Prevent multiple triggers during a single scroll event

        const likeButton = document.getElementById('likeButton');
        const likeCountElement = document.getElementById('likes-count'); // Likes count span
        const commentCountElement = document.querySelector('comments-count'); // Comments count span

        // Function to load a video
        const loadVideo = (index) => {
            currentVideoElement.src = "{{ asset('') }}" + videos[index].file_path;
            currentVideoElement.play().catch(err => console.warn("Playback failed:", err));
            // Fetch updated likes and comments count for the current video
            // fetch(`/videos/${videos[index].id}/data`)
            //     .then(response => response.json())
            //     .then(data => {
            //         // Update the like and comment counts in the UI
            //         likeCountElement.textContent = `${data.likes_count} Likes`;
            //         commentCountElement.textContent = `${data.comments_count} Comments`;
            //     })
            //     .catch(error => console.error("Error fetching video data:", error));
        };

        // Function to check if a video is in the viewport
        const isInViewport = (video) => {
            const rect = video.getBoundingClientRect();
            return rect.top >= 0 && rect.bottom <= window.innerHeight;
        };

        // Mute/Unmute and Play/Pause button logic
        const playPauseButton = document.querySelector('.play-pause');
        const muteUnmuteButton = document.querySelector('.mute-unmute');

        // Play/Pause button click handler
        playPauseButton.addEventListener('click', () => {
            if (currentVideoElement.paused) {
                currentVideoElement.play().catch(err => console.warn("Playback failed:", err));
                playPauseButton.textContent = '⏸️';
            } else {
                currentVideoElement.pause();
                playPauseButton.textContent = '▶️';
            }
        });

        // Mute/Unmute button click handler
        muteUnmuteButton.addEventListener('click', () => {
            if (currentVideoElement.muted) {
                currentVideoElement.muted = false;
                muteUnmuteButton.textContent = '🔊';
            } else {
                currentVideoElement.muted = true;
                muteUnmuteButton.textContent = '🔇';
            }
        });

        // Scroll event handler
        const handleScroll = (event) => {
            if (isScrolling) return; // Ignore scroll events while already transitioning

            if (event.deltaY > 0) {
                // Scroll down
                if (currentIndex < videos.length - 1) {
                    currentIndex++;
                    loadVideo(currentIndex);
                    isScrolling = true;
                }
            } else if (event.deltaY < 0) {
                // Scroll up
                if (currentIndex > 0) {
                    currentIndex--;
                    loadVideo(currentIndex);
                    isScrolling = true;
                }
            }

            // Reset scroll lock after a short delay
            setTimeout(() => {
                isScrolling = false;
            }, 500); // Adjust debounce time as needed
        };

        // Attach scroll event listener to the document
        document.addEventListener('wheel', handleScroll);


        // Handle Play/Pause with Spacebar key press
        document.addEventListener('keydown', (event) => {
            if (event.key === ' ') {
                if (currentVideoElement.paused) {
                    currentVideoElement.play().catch(err => console.warn("Playback failed:", err));
                    playPauseButton.textContent = '⏸️';
                } else {
                    currentVideoElement.pause();
                    playPauseButton.textContent = '▶️';
                }
                event.preventDefault(); // Prevent scrolling on spacebar
            }

            // Navigate to the next video on Arrow Down
            if (event.key === 'ArrowDown') {
                if (currentIndex < videos.length - 1) {
                    currentIndex++;
                    loadVideo(currentIndex); // Load next video
                }
            }

            // Navigate to the previous video on Arrow Up
            if (event.key === 'ArrowUp') {
                if (currentIndex > 0) {
                    currentIndex--;
                    loadVideo(currentIndex); // Load previous video
                }
            }
        });

        // Initially load the first video
        loadVideo(currentIndex);


        const showToast = (message, type = 'success') => {
            Toastify({
                text: message,
                duration: 3000,
                gravity: "top",
                position: "center",
                backgroundColor: type === 'success' ? "green" : "red",
                stopOnFocus: true,
            }).showToast();
        };

        const showAlert = (message, type = 'success') => {
            Swal.fire({
                icon: type,
                title: message,
                timer: 3000,
                showConfirmButton: false,
            });
        };

        // Like button handler
        // const likeButton = document.getElementById('likeButton');
        likeButton.addEventListener('click', () => {
            const videoId = likeButton.getAttribute('data-video-id');
            const isLiked = likeButton.getAttribute('data-liked') === 'true';

            fetch(`/videos/${videos[currentIndex].id}/like`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            })
                .then(response => response.json())
                .then(data => {
                    Swal.fire({
                        title: 'Success',
                        text: data.message,
                        icon: 'success',
                        timer: 2000, // Automatically close after 2 seconds
                        timerProgressBar: true,
                        showConfirmButton: false, // Hide the confirm button
                    }).then(() => {
                        // Update the UI
                        likeButton.textContent = isLiked ? '♡' : '❤️'; // Toggle button text
                        likeButton.setAttribute('data-liked', isLiked ? 'false' : 'true');
                        loadVideo(currentIndex); // Reload the current video
                        Swal.close();
                    });
                })
                .catch(error => {
                    showAlert("Failed to like the video.", 'error');
                    console.error(error);
                });
        });

        // Comment form submission
        const commentForm = document.getElementById('commentForm');
        commentForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const comment = commentForm.comment.value;

            fetch(`/videos/${videos[currentIndex].id}/comment`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ comment }),
            })
                .then(response => response.json())
                .then(data => {
                    Swal.fire({
                        title: 'Success',
                        text: data.message,
                        icon: 'success',
                        timer: 2000, // Automatically close after 2 seconds
                        timerProgressBar: true,
                        showConfirmButton: false, // Hide the confirm button
                    }).then(() => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('commentModal'));
                        if (modal) {
                            console.log('Modal instance found:', modal); // Debugging log
                            modal.hide(); // Hide the modal
                        } else {
                            console.error('Modal instance not found!'); // Log error if modal instance is not found
                        }
                        // Remove remaining backdrop if any
                        document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());

                        commentForm.reset();
                        loadVideo(currentIndex); // Reload the current video
                        Swal.close();
                    });
                })
                .catch(error => {
                    showAlert("Failed to submit comment.", 'error');
                    console.error(error);
                });
        });

    });
</script>
@endsection
