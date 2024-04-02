// Place this script in your HTML file or include it where necessary
document.addEventListener('DOMContentLoaded', function () {
    const searchBox = document.getElementById('search-box');
    const expositionsContainer = document.getElementById('expositions');

    searchBox.addEventListener('input', function () {
        const query = searchBox.value.trim();

        // Make an AJAX request to the server
        fetch('/client/searchExpoByName?query=' + query)


            .then(response => response.json())
            .then(data => {
                // Clear the previous search results
                expositionsContainer.innerHTML = '';

                // Display the search results
                data.forEach(exposition => {
                    const card = `
                        <div class="col-md-4 col-sm-6 mb-30">
                            <div class="card h-100 d-flex flex-column">
                                <a class="card-img-tiles" href="#" data-abc="true">
                                    <div class="inner">
                                        <div class="main-img">
                                            <img src="${exposition.image ? exposition.image : 'placeholder.jpg'}" alt="${exposition.nom}" class="exposition-image" style="width: 100%; height: auto;">
                                        </div>
                                    </div>
                                </a>
                                <div class="card-body text-center d-flex flex-column">
                                    <h4 class="card-title">${exposition.nom}</h4>
                                    <p class="text-muted"><br>Theme: ${exposition.theme}<br></p>
                                    <div class="mt-auto">
                                        <a href="${exposition.link}" class="btn item-btn btn-primary display-7">Voir Détails</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    expositionsContainer.innerHTML += card;
                });
            })
            .catch(error => console.error('Error:', error));
    });
});
