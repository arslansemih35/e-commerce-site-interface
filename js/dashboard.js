// Sayfa yüklendiğinde
document.addEventListener('DOMContentLoaded', function() {
    // Kullanıcı bilgilerini yükle
    loadUserInfo();
    
    // Menü işlemleri
    setupMenuNavigation();
    
    // Sipariş filtreleri
    setupOrderFilters();
    
    // Profil formu işlemleri
    setupProfileForm();
});

// Kullanıcı bilgilerini yükleme
function loadUserInfo() {
    // API'den kullanıcı bilgilerini al
    fetch('php/get_user_info.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('userName').textContent = data.user.name;
                document.getElementById('userEmail').textContent = data.user.email;
            }
        })
        .catch(error => console.error('Kullanıcı bilgileri yüklenemedi:', error));
}

// Menü navigasyonu
function setupMenuNavigation() {
    const menuLinks = document.querySelectorAll('.dashboard-menu a');
    const sections = document.querySelectorAll('.dashboard-section');

    menuLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Aktif menü öğesini güncelle
            menuLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            
            // İlgili bölümü göster
            const targetId = this.getAttribute('href').substring(1);
            sections.forEach(section => {
                section.classList.remove('active');
                if (section.id === targetId) {
                    section.classList.add('active');
                }
            });
        });
    });
}

// Sipariş filtreleri
function setupOrderFilters() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Aktif filtre butonunu güncelle
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Siparişleri filtrele
            const filter = this.textContent.toLowerCase();
            loadOrders(filter);
        });
    });
}

// Siparişleri yükleme
function loadOrders(filter = 'tümü') {
    fetch(`php/get_orders.php?filter=${filter}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const ordersList = document.querySelector('.orders-list');
                ordersList.innerHTML = ''; // Mevcut siparişleri temizle
                
                data.orders.forEach(order => {
                    const orderElement = createOrderElement(order);
                    ordersList.appendChild(orderElement);
                });
            }
        })
        .catch(error => console.error('Siparişler yüklenemedi:', error));
}

// Sipariş elementi oluşturma
function createOrderElement(order) {
    const div = document.createElement('div');
    div.className = 'order-item';
    div.innerHTML = `
        <div class="order-header">
            <span>Sipariş No: #${order.id}</span>
            <span>Tarih: ${order.date}</span>
        </div>
        <div class="order-details">
            <p>Toplam: ₺${order.total}</p>
            <p>Durum: <span class="status ${order.status.toLowerCase()}">${order.status}</span></p>
        </div>
        <button class="btn-view" onclick="viewOrder(${order.id})">Görüntüle</button>
    `;
    return div;
}

// Profil formu işlemleri
function setupProfileForm() {
    const form = document.querySelector('.profile-form');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('php/update_profile.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Profil bilgileri güncellendi');
            } else {
                alert('Güncelleme başarısız: ' + data.message);
            }
        })
        .catch(error => console.error('Profil güncellenemedi:', error));
    });
}

// Sipariş görüntüleme
function viewOrder(orderId) {
    window.location.href = `order_details.html?id=${orderId}`;
}

// Çıkış yapma
function logout() {
    fetch('php/logout.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'login.html';
            }
        })
        .catch(error => console.error('Çıkış yapılamadı:', error));
} 