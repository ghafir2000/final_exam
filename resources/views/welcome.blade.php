<title>@lang('Welcome to Dr.Pet')</title>



<body>
    <style>
        body {
            margin: 0;
            overflow: hidden;
        }

        video#background-video {
            position: absolute;
            top: 50%;
            left: 50%;
            width: auto; /* Maintain width based on aspect ratio */
            height: auto; /* Maintain height based on aspect ratio */
            max-width: 50%; /* Restrict the maximum width */
            max-height: 50%; /* Restrict the maximum height */
            transform: translate(-50%, -50%); /* Center the video */
            /* z-index: -1; Behind everything */
            opacity: 0;
            transition: opacity 2s ease-in-out; /* Fade-in effect */
        }

        video#background-video.appear {
            opacity: 1;
        }
    </style>
</head>

<body>
    <!-- Background Video -->
    <video id="background-video" autoplay muted>
        <source src="\videos\welcome_loading_animation.mp4" type="video/mp4">
        @lang('Your browser does not support the video tag.')
    </video>

    <script>
        // Fade in video
        window.addEventListener('load', () => {
            const video = document.getElementById('background-video');

            video.classList.add('appear');

            // Redirect after the video completes its first loop
            video.addEventListener('ended', () => {
                window.location.href = "{{ route('blog.index') }}"; // Update for the correct login route
            });

            // Redirect when the video is clicked
            video.addEventListener('click', () => {
                window.location.href = "{{ route('blog.index') }}";
            });
        });
    </script>
</body>

</html>

