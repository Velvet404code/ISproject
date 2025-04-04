document.addEventListener('DOMContentLoaded', () => {
    const userNameDisplay = document.getElementById('userNameDisplay');
    const reportedItemsList = document.getElementById('reportedItemsList');
    const lostItemForm = document.getElementById('lostItemForm');
    const foundItemForm = document.getElementById('foundItemForm');

    // Simulate fetching user data
    const userName = 'Maximus Maina';
    userNameDisplay.textContent = userName;

    // Handle lost item form submission
    lostItemForm.addEventListener('submit', (event) => {
        event.preventDefault();
        const itemName = document.getElementById('lost-item-name').value;
        const description = document.getElementById('lost-description').value;
        const picture = document.getElementById('lost-picture').files[0];
        const duration = document.getElementById('lost-duration').value;
        const location = document.getElementById('lost-location').value;
        const contact = document.getElementById('lost-contact').value;

        // Add the reported item to the list
        const listItem = document.createElement('li');
        listItem.textContent = `${itemName} - ${description} (Contact: ${contact})`;
        reportedItemsList.appendChild(listItem);

        // Clear the form
        lostItemForm.reset();
    });

    // Handle found item form submission
    foundItemForm.addEventListener('submit', (event) => {
        event.preventDefault();
        const itemName = document.getElementById('found-item-name').value;
        const description = document.getElementById('found-description').value;
        const picture = document.getElementById('found-picture').files[0];
        const duration = document.getElementById('found-duration').value;
        const location = document.getElementById('found-location').value;
        const contact = document.getElementById('found-contact').value;

        // Simulate reporting the found item
        alert(`Found item reported: ${itemName} - ${description} (Contact: ${contact})`);

        // Clear the form
        foundItemForm.reset();
    });

    document.getElementById('profile-picture').addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profile-picture-preview').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
});
