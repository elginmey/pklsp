document.addEventListener('DOMContentLoaded', function() {
    function loadGalleryImages() {
        fetch('get_gallery_data.php')
            .then(response => response.json())
            .then(data => {
                const imageContainer = document.querySelector('.image-container');
                imageContainer.innerHTML = ''; // Bersihkan container sebelum menambahkan gambar baru
                data.forEach(item => {
                    const cardHtml = `
                        <div class="card image ${item.category}">
                            <a href="${item.image_url}">
                                <img src="${item.image_url}" class="card-img-top" alt="${item.title}" />
                            </a>
                            <div class="card-body">
                                <h5 class="card-title">${item.title}</h5>
                                <p class="card-text">${item.description}</p>
                            </div>
                        </div>
                    `;
                    imageContainer.insertAdjacentHTML('beforeend', cardHtml);
                });
            })
            .catch(error => console.error('Error:', error));
    }

    loadGalleryImages();

    // Tambahkan event listener untuk tombol filter
    const filterButtons = document.querySelectorAll('.buttons');
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            const images = document.querySelectorAll('.image');
            
            images.forEach(image => {
                if (filter === 'all' || image.classList.contains(filter)) {
                    image.style.display = 'block';
                } else {
                    image.style.display = 'none';
                }
            });
        });
    });
});