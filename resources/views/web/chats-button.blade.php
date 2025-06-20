<!-- HTML for the Circular Button -->
<div id="chat-floating-btn-container">
    <button id="chat-floating-btn" class="btn btn-primary rounded-circle shadow-lg">
        <i class="fas fa-comments"></i>
    </button>
</div>

<!-- CSS for Styling and Animation -->
<style>
    #chat-floating-btn-container {
        position: fixed;
        bottom: 20px;
        left: 20px;
        z-index: 1000;
    }

    #chat-floating-btn {
        width: 50px;
        height: 50px;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: #007bff;
        transition: transform 0.3s ease;
    }

    #chat-floating-btn:hover {
        transform: scale(1.1);
    }
</style>

<!-- JavaScript for Button Click Event -->
<script>
    document.getElementById('chat-floating-btn').addEventListener('click', function() {
        window.location.href = '{{ route('chat.index') }}';
    });
</script>

