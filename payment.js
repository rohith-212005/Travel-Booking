// payment.js (optional, updated)
document.addEventListener('DOMContentLoaded', () => {
    // Get the ticket details from the URL
    const urlParams = new URLSearchParams(window.location.search);
    const ticketName = urlParams.get('ticketName');
    const ticketPrice = urlParams.get('ticketPrice');

    // Set the ticket details on the payment page
    if (ticketName && ticketPrice) {
        document.getElementById('ticket-name').textContent = ticketName;
        document.getElementById('ticket-price').textContent = ticketPrice;
    }

    // Populate hidden fields in the form
    if (ticketName) document.getElementById('ticketName').value = ticketName;
    if (ticketPrice) document.getElementById('ticketPrice').value = ticketPrice;
});