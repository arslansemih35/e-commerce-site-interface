// Sayfa yüklendiğinde
document.addEventListener('DOMContentLoaded', function() {
    // Ürünleri yükle
    loadProducts();
    
    // Kategori filtrelerini ayarla
    setupCategoryFilters();
    
    // Sıralama seçeneğini ayarla
    setupSorting();
    
    // Görünüm seçeneklerini ayarla
    setupViewOptions();
    
    // Kampanyaları yükle
    loadCampaigns();
    
    // Müşteri yorumlarını yükle
    loadTestimonials();
    
    // Sayfa yukarı çıkma butonunu ayarla
    setupScrollTop();
    
    // WhatsApp butonunu ayarla
    setupWhatsApp();
    
    // Bülten formunu ayarla
    setupNewsletterForm();
});

// Ürünleri yükleme
function loadProducts(category = 'all', sort = 'newest', view = 'grid') {
    fetch(`php/get_products.php?category=${category}&sort=${sort}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayProducts(data.products, view);
                updatePagination(data.total_pages, data.current_page);
            } else {
                showError('Ürünler yüklenirken bir hata oluştu.');
            }
        })
        .catch(error => {
            showError('Ürünler yüklenirken bir hata oluştu.');
        });
}

// Ürünleri görüntüleme
function displayProducts(products, view) {
    const container = document.querySelector('.products-grid');
    container.className = `products-grid ${view}-view`;
    
    container.innerHTML = products.map(product => `
        <div class="product-card">
            <div class="product-image">
                <img src="${product.image_url || 'images/default-product.jpg'}" alt="${product.name}">
                ${product.is_organic ? '<span class="organic-badge">Organik</span>' : ''}
            </div>
            <div class="product-info">
                <h3>${product.name}</h3>
                <p class="product-description">${product.description}</p>
                <div class="product-details">
                    <div class="product-price">
                        ${product.sale_price ? `
                            <span class="original-price">${formatPrice(product.price)}</span>
                            <span class="sale-price">${formatPrice(product.sale_price)}</span>
                        ` : formatPrice(product.price)}
                    </div>
                    <span class="product-weight">${product.weight} ${product.unit}</span>
                </div>
                <div class="product-meta">
                    <p><i class="fas fa-map-marker-alt"></i> ${product.origin}</p>
                    <p><i class="fas fa-calendar"></i> Üretim: ${formatDate(product.production_date)}</p>
                    <p><i class="fas fa-star"></i> ${product.rating.toFixed(1)} (${product.review_count} değerlendirme)</p>
                </div>
                <button class="btn-add-cart" onclick="addToCart(${product.id})">
                    <i class="fas fa-shopping-cart"></i> Sepete Ekle
                </button>
            </div>
        </div>
    `).join('');
}

// Ürün elementi oluşturma
function createProductElement(product) {
    const div = document.createElement('div');
    div.className = 'product-card';
    div.innerHTML = `
        <div class="product-image">
            <img src="${product.image_url}" alt="${product.name}">
            ${product.is_organic ? '<span class="organic-badge">Organik</span>' : ''}
        </div>
        <div class="product-info">
            <h3>${product.name}</h3>
            <p class="product-description">${product.description}</p>
            <div class="product-details">
                <span class="product-price">₺${product.price}</span>
                <span class="product-weight">${product.weight} ${product.unit}</span>
            </div>
            <div class="product-meta">
                <span class="product-origin"><i class="fas fa-map-marker-alt"></i> ${product.origin}</span>
                <span class="product-date"><i class="fas fa-calendar"></i> ${formatDate(product.production_date)}</span>
            </div>
            <button class="btn-add-cart" onclick="addToCart(${product.id})">
                <i class="fas fa-shopping-cart"></i> Sepete Ekle
            </button>
        </div>
    `;
    return div;
}

// Kategori filtrelerini ayarlama
function setupCategoryFilters() {
    const filters = document.querySelectorAll('.filter-btn');
    filters.forEach(filter => {
        filter.addEventListener('click', function() {
            filters.forEach(f => f.classList.remove('active'));
            this.classList.add('active');
            loadProducts(this.dataset.category);
        });
    });
}

// Sıralama seçeneğini ayarlama
function setupSorting() {
    const sortSelect = document.getElementById('sort-select');
    sortSelect.addEventListener('change', function() {
        loadProducts(document.querySelector('.filter-btn.active').dataset.category, this.value);
    });
}

// Görünüm seçeneklerini ayarlama
function setupViewOptions() {
    const viewButtons = document.querySelectorAll('.view-btn');
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            viewButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            loadProducts(
                document.querySelector('.filter-btn.active').dataset.category,
                document.getElementById('sort-select').value,
                this.dataset.view
            );
        });
    });
}

// Kampanyaları yükleme
function loadCampaigns() {
    fetch('php/get_campaigns.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayCampaigns(data.campaigns);
                setupCampaignSlider();
            }
        })
        .catch(error => {
            console.error('Kampanyalar yüklenirken hata:', error);
        });
}

// Kampanyaları görüntüleme
function displayCampaigns(campaigns) {
    const container = document.querySelector('.campaign-slider');
    container.innerHTML = campaigns.map(campaign => `
        <div class="campaign-card">
            <img src="${campaign.image_url}" alt="${campaign.title}">
            <div class="campaign-content">
                <h3>${campaign.title}</h3>
                <p>${campaign.description}</p>
                <div class="campaign-meta">
                    <span class="discount">%${campaign.discount}</span>
                    <span class="valid-until">${formatDate(campaign.valid_until)}'e kadar</span>
                </div>
                <a href="${campaign.link}" class="btn-primary">Kampanyaya Git</a>
            </div>
        </div>
    `).join('');
}

// Kampanya slider'ını ayarlama
function setupCampaignSlider() {
    const slider = document.querySelector('.campaign-slider');
    let currentSlide = 0;
    const slides = slider.children;
    
    function showSlide(index) {
        Array.from(slides).forEach(slide => slide.style.display = 'none');
        slides[index].style.display = 'block';
    }
    
    function nextSlide() {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
    }
    
    function prevSlide() {
        currentSlide = (currentSlide - 1 + slides.length) % slides.length;
        showSlide(currentSlide);
    }
    
    // Slider kontrollerini ayarla
    document.querySelector('.campaigns .prev-btn').addEventListener('click', prevSlide);
    document.querySelector('.campaigns .next-btn').addEventListener('click', nextSlide);
    
    // İlk slide'ı göster
    showSlide(0);
    
    // Otomatik geçiş
    setInterval(nextSlide, 5000);
}

// Müşteri yorumlarını yükleme
function loadTestimonials() {
    fetch('php/get_testimonials.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayTestimonials(data.testimonials);
                setupTestimonialSlider();
            }
        })
        .catch(error => {
            console.error('Yorumlar yüklenirken hata:', error);
        });
}

// Yorumları görüntüleme
function displayTestimonials(testimonials) {
    const container = document.querySelector('.testimonials-slider');
    container.innerHTML = testimonials.map(testimonial => `
        <div class="testimonial-card">
            <div class="testimonial-header">
                <img src="${testimonial.user_image || 'images/default-avatar.jpg'}" alt="${testimonial.user_name}">
                <div class="testimonial-info">
                    <h4>${testimonial.user_name}</h4>
                    <div class="rating">
                        ${generateStars(testimonial.rating)}
                    </div>
                </div>
            </div>
            <p class="testimonial-text">${testimonial.comment}</p>
            <span class="testimonial-date">${formatDate(testimonial.created_at)}</span>
        </div>
    `).join('');
}

// Yorum slider'ını ayarlama
function setupTestimonialSlider() {
    const slider = document.querySelector('.testimonials-slider');
    let currentSlide = 0;
    const slides = slider.children;
    
    function showSlide(index) {
        Array.from(slides).forEach(slide => slide.style.display = 'none');
        slides[index].style.display = 'block';
    }
    
    function nextSlide() {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
    }
    
    function prevSlide() {
        currentSlide = (currentSlide - 1 + slides.length) % slides.length;
        showSlide(currentSlide);
    }
    
    // Slider kontrollerini ayarla
    document.querySelector('.testimonials .prev-btn').addEventListener('click', prevSlide);
    document.querySelector('.testimonials .next-btn').addEventListener('click', nextSlide);
    
    // İlk slide'ı göster
    showSlide(0);
    
    // Otomatik geçiş
    setInterval(nextSlide, 5000);
}

// Sayfa yukarı çıkma butonunu ayarlama
function setupScrollTop() {
    const scrollBtn = document.querySelector('.scroll-top');
    
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            scrollBtn.style.display = 'block';
        } else {
            scrollBtn.style.display = 'none';
        }
    });
    
    scrollBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// WhatsApp butonunu ayarlama
function setupWhatsApp() {
    const whatsappBtn = document.querySelector('.whatsapp');
    whatsappBtn.addEventListener('click', function() {
        window.open('https://wa.me/905555555555', '_blank');
    });
}

// Bülten formunu ayarlama
function setupNewsletterForm() {
    const form = document.querySelector('.newsletter-form');
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = this.querySelector('input[type="email"]').value;
        
        fetch('php/subscribe_newsletter.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess('Bülten aboneliğiniz başarıyla tamamlandı!');
                this.reset();
            } else {
                showError(data.message || 'Bir hata oluştu.');
            }
        })
        .catch(error => {
            showError('Bir hata oluştu.');
        });
    });
}

// Yardımcı fonksiyonlar
function formatPrice(price) {
    return new Intl.NumberFormat('tr-TR', {
        style: 'currency',
        currency: 'TRY'
    }).format(price);
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('tr-TR');
}

function generateStars(rating) {
    return Array(5).fill('').map((_, index) => `
        <i class="fas fa-star ${index < rating ? 'filled' : ''}"></i>
    `).join('');
}

function showSuccess(message) {
    // Başarı mesajı göster
    const toast = document.createElement('div');
    toast.className = 'toast success';
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

function showError(message) {
    // Hata mesajı göster
    const toast = document.createElement('div');
    toast.className = 'toast error';
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Sepete ürün ekleme
function addToCart(productId) {
    fetch('php/add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ product_id: productId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount(data.cart_count);
            showSuccess('Ürün sepete eklendi!');
        } else {
            showError(data.message || 'Ürün sepete eklenirken bir hata oluştu.');
        }
    })
    .catch(error => {
        showError('Ürün sepete eklenirken bir hata oluştu.');
    });
}

// Sepet sayısını güncelleme
function updateCartCount(count) {
    const cartCount = document.querySelector('.cart-count');
    cartCount.textContent = count;
} 