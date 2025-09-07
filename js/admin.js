// Satış Grafiği
const salesCtx = document.getElementById('salesChart').getContext('2d');
const salesChart = new Chart(salesCtx, {
    type: 'line',
    data: {
        labels: ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran'],
        datasets: [{
            label: 'Satışlar',
            data: [12000, 19000, 15000, 25000, 22000, 30000],
            borderColor: '#3498db',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            }
        }
    }
});

// Popüler Ürünler Grafiği
const productsCtx = document.getElementById('productsChart').getContext('2d');
const productsChart = new Chart(productsCtx, {
    type: 'doughnut',
    data: {
        labels: ['Laptop', 'Telefon', 'Tablet', 'Aksesuar'],
        datasets: [{
            data: [300, 250, 200, 150],
            backgroundColor: [
                '#3498db',
                '#2ecc71',
                '#e74c3c',
                '#f1c40f'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            }
        }
    }
});

// Arama Fonksiyonu
document.querySelector('.admin-search button').addEventListener('click', function() {
    const searchTerm = document.querySelector('.admin-search input').value;
    // Arama işlemi burada yapılacak
    console.log('Arama yapılıyor:', searchTerm);
});

// Tablo Sıralama
document.querySelectorAll('th').forEach(header => {
    header.addEventListener('click', function() {
        const table = this.closest('table');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const index = Array.from(this.parentElement.children).indexOf(this);
        
        rows.sort((a, b) => {
            const aValue = a.children[index].textContent;
            const bValue = b.children[index].textContent;
            return aValue.localeCompare(bValue);
        });
        
        rows.forEach(row => tbody.appendChild(row));
    });
});

// Buton İşlemleri
document.querySelectorAll('.btn-view, .btn-edit').forEach(button => {
    button.addEventListener('click', function() {
        const orderId = this.closest('tr').querySelector('td').textContent;
        const action = this.classList.contains('btn-view') ? 'görüntüleme' : 'düzenleme';
        console.log(`${orderId} numaralı sipariş ${action} işlemi başlatıldı`);
    });
});

// Responsive Menü
const menuToggle = document.createElement('button');
menuToggle.className = 'menu-toggle';
menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
document.querySelector('.admin-header').prepend(menuToggle);

menuToggle.addEventListener('click', function() {
    document.querySelector('.admin-sidebar').classList.toggle('active');
}); 