document.getElementById('searchButton').addEventListener('click', function() {
    const query = document.getElementById('searchInput').value;
    // Simulate search results
    const results = searchItems(query);
    displayResults(results);
});

document.getElementById('foundSearchButton').addEventListener('click', function() {
    const query = document.getElementById('foundSearchInput').value;
    // Simulate search results for found items
    const results = searchFoundItems(query);
    displayFoundResults(results);
});

document.getElementById('testimonialForm').addEventListener('submit', function(event) {
    event.preventDefault();
    
    const name = document.getElementById('name').value;
    const message = document.getElementById('message').value;
    
    const testimonial = { name, message };
    
    let testimonials = JSON.parse(localStorage.getItem('testimonials')) || [];
    testimonials.push(testimonial);
    localStorage.setItem('testimonials', JSON.stringify(testimonials));
    
    document.getElementById('testimonialForm').reset();
    alert('Thank you for your testimonial!');
});

function searchItems(query) {
    // This is a mock function. Replace with actual search logic.
    const items = [
        { id: 1, image: 'image.7.webp', name: 'Lost a black wallet', description: 'On 3/10/2025,a black leather wallet was lost in Karen park at 10:00 AM' },
        { id: 2, image: 'image.9.jpeg', name: 'Lost a blue bag', description: 'On 4/12/2025, a blue bag was lost in Student centre at 2:00 PM' }
    ];
    return items.filter(item => item.name.toLowerCase().includes(query.toLowerCase()));
}

function searchFoundItems(query) {
    // This is a mock function. Replace with actual search logic.
    const foundItems = [
        { id: 3, image: 'image.8.jpeg', title: 'Found a set of keys', description: 'On 3/12/2025, a set of house keys was found in University library at 12:00 PM' },
        { id: 1, image: 'image.10.jpeg', title: 'Found a silver watch', description: 'On 4/10/2025, a silver watch was found in Runda park at 10:00 AM' }
    ];
    return foundItems.filter(item => item.title.toLowerCase().includes(query.toLowerCase()));
}

function displayResults(results) {
    const container = document.getElementById('lostItemsContainer');
    container.innerHTML = ''; // Clear previous results
    if (results.length === 0) {
        container.innerHTML = '<p>No items matched your search.</p>'; // Display message if no results
    } else {
        results.forEach(item => {
            const itemDiv = document.createElement('div');
            itemDiv.className = 'lost-item';
            itemDiv.innerHTML = `<h3>${item.name}</h3><p>${item.description}</p><img src="${item.image}" alt="${item.name}" class="item-image">`;
            container.appendChild(itemDiv);
        });
    }
}

function displayFoundResults(results) {
    const container = document.getElementById('foundItemsContainer');
    container.innerHTML = ''; // Clear previous results
    if (results.length === 0) {
        container.innerHTML = '<p>No found items matched your search.</p>'; // Display message if no results
    } else {
        results.forEach(item => {
            const itemDiv = document.createElement('div');
            itemDiv.className = 'found-item';
            itemDiv.innerHTML = `<h3>${item.title}</h3><p>${item.description}</p><img src="${item.image}" alt="${item.title}" class="item-image">`;
            container.appendChild(itemDiv);
        });
    }
}

// Simulate recent updates
const recentUpdates = [
    { id: 3, image: 'image.8.jpeg', title: 'Found a set of keys', description: 'A set of house keys was found in University library at 12:00 PM' },
    { id: 1, image: 'image.10.jpeg', title: 'Found a silver watch', description: 'A silver watch was found in Runda park at 10:00 AM' }
];

function displayRecentUpdates() {
    const container = document.getElementById('recentUpdatesContainer');
    container.innerHTML = ''; // Clear previous updates
    recentUpdates.forEach(update => {
        const updateDiv = document.createElement('div');
        updateDiv.className = 'recent-update-item';
        updateDiv.innerHTML = `<h3>${update.title}</h3><p>${update.description}</p><img src="${update.image}" alt="${update.title}" class="item-image">`;
        container.appendChild(updateDiv);
    });
}

// Display recent updates on page load
document.addEventListener('DOMContentLoaded', displayRecentUpdates);
