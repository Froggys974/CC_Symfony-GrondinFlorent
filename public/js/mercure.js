
const eventSource = new EventSource('http://localhost:3000/.well-known/mercure?topic=post/1');

eventSource.onmessage = function(event) {
    const data = JSON.parse(event.data);
    const likesCountElement = document.getElementById('likes-count');

    likesCountElement.innerText = data.likesCount;
};
