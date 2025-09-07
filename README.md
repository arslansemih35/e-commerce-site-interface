# 🛒 E-Ticaret Web Sitesi

Modern ve responsive bir e-ticaret platformu. Organik gıda ürünlerinin satışı için geliştirilmiş full-stack web uygulaması.

## 🎯 Proje Hakkında

Bu proje, organik gıda ürünlerinin online satışı için geliştirilmiş kapsamlı bir e-ticaret sistemidir. Kullanıcılar zeytinyağı, bal, süt ürünleri, yumurta gibi organik gıdaları inceleyebilir, sepete ekleyebilir ve satın alabilirler.

## ✨ Özellikler

### 🛍️ E-Ticaret Fonksiyonları
- **Ürün Kataloğu**: Kategorize edilmiş ürün listesi (Zeytinyağı, Bal, Süt Ürünleri, Yumurta, Peynir, Tereyağı)
- **Filtreleme ve Sıralama**: Ürünleri kategoriye göre filtreleme ve fiyata göre sıralama
- **Sepet Yönetimi**: Ürün ekleme/çıkarma ve sepet güncellemeleri
- **Kullanıcı Sistemi**: Kayıt olma, giriş yapma ve profil yönetimi
- **Sipariş İşlemi**: Güvenli sipariş oluşturma ve takip sistemi
- **Kupon Sistemi**: İndirim kuponları ve kampanya desteği
- **Favori Ürünler**: Beğenilen ürünleri kaydetme
- **Newsletter**: E-posta aboneliği sistemi

### 🎨 Kullanıcı Arayüzü
- **Responsive Tasarım**: Tüm cihazlarda uyumlu görünüm
- **Modern UI**: Sade ve kullanıcı dostu arayüz
- **Mobil Hamburger Menü**: Touch-friendly navigasyon
- **Toast Bildirimler**: Kullanıcı etkileşim geri bildirimleri
- **Smooth Animations**: Geçiş efektleri ve animasyonlar

### 🔧 Admin Panel
- **Dashboard**: Satış istatistikleri ve genel bakış
- **Ürün Yönetimi**: Ürün ekleme, düzenleme ve silme
- **Sipariş Yönetimi**: Sipariş durumu güncelleme ve takip
- **Kullanıcı Yönetimi**: Müşteri hesapları kontrolü
- **Kampanya Yönetimi**: İndirim kampanyaları düzenleme

## 🚀 Teknoloji Yığını

### Frontend
- **HTML5**: Semantic markup ve erişilebilirlik
- **CSS3**: Modern styling, Flexbox ve Grid Layout
- **Vanilla JavaScript**: DOM manipülasyonu ve AJAX
- **Font Awesome**: İkon kütüphanesi
- **Responsive Design**: Mobile-first yaklaşım

### Backend
- **PHP**: Server-side programlama
- **MySQL**: İlişkisel veritabanı
- **Session Management**: Kullanıcı oturum yönetimi
- **AJAX**: Asenkron veri işleme

### Özellikler
- **Form Validation**: Client ve server-side doğrulama
- **Security**: SQL injection koruması
- **API Endpoints**: RESTful API yapısı
- **Dynamic Content**: JavaScript ile dinamik içerik

## 📁 Proje Yapısı

```
project/
├── 📁 admin/              # Admin paneli
│   └── index.html         # Admin dashboard
├── 📁 css/                # Stil dosyaları
│   ├── style.css          # Ana stil dosyası
│   ├── admin.css          # Admin panel stilleri
│   ├── dashboard.css      # Kullanıcı dashboard
│   └── login.css          # Login sayfası stilleri
├── 📁 images/             # Görsel dosyalar
│   └── farm.jpg          # Çiftlik görseli
├── 📁 js/                 # JavaScript dosyaları
│   ├── main.js           # Ana JavaScript
│   ├── admin.js          # Admin panel JS
│   └── dashboard.js      # Dashboard JS
├── 📁 php/                # Backend API dosyaları
│   ├── config.php        # Veritabanı bağlantısı
│   ├── login.php         # Kullanıcı girişi
│   ├── register.php      # Kullanıcı kaydı
│   ├── get_products.php  # Ürün listesi API
│   ├── add_to_cart.php   # Sepete ekleme
│   ├── get_cart.php      # Sepet bilgileri
│   ├── update_cart.php   # Sepet güncelleme
│   ├── create_order.php  # Sipariş oluşturma
│   ├── apply_coupon.php  # Kupon uygulama
│   └── ...               # Diğer API dosyaları
├── index.html             # Ana sayfa
├── login.html            # Giriş sayfası
├── register.html         # Kayıt sayfası
├── dashboard.html        # Kullanıcı paneli
├── database.sql          # Veritabanı şeması
└── README.md            # Proje dokümantasyonu
```

## 🛍️ Ürün Kategorileri

- **🫒 Zeytinyağı**: Organik soğuk sıkım zeytinyağı
- **🍯 Bal Çeşitleri**: Çiçek balı ve diğer bal türleri  
- **🥛 Süt Ürünleri**: Taze organik süt
- **🥚 Yumurta**: Organik köy yumurtası
- **🧀 Peynir**: Köy peyniri çeşitleri
- **🧈 Tereyağı**: Organik ev yapımı tereyağı

## 🔧 Kurulum

### Gereksinimler
- Apache/Nginx web sunucu
- PHP 7.4+
- MySQL 5.7+ 
- Modern web tarayıcı

### Kurulum Adımları

1. **Projeyi klonlayın**
   ```bash
   git clone https://github.com/kullanici_adi/proje_adi.git
   cd proje_adi
   ```

2. **Veritabanını kurun**
   ```bash
   mysql -u root -p < database.sql
   ```

3. **Veritabanı ayarlarını yapın**
   ```php
   // php/config.php
   $servername = "localhost";
   $username = "veritabani_kullanici";
   $password = "veritabani_sifre";
   $dbname = "eticaret_db";
   ```

4. **Web sunucusunda çalıştırın**
   - XAMPP/WAMP kullanıyorsanız `htdocs` klasörüne kopyalayın
   - Canlı sunucuya yükleyip domain'e yönlendirin

## 🎮 Kullanım

### Müşteri İşlemleri
1. **Ana Sayfa**: Ürünleri görüntüleyin ve kampanyaları inceleyin
2. **Kayıt/Giriş**: Yeni hesap oluşturun veya mevcut hesabınızla giriş yapın
3. **Ürün Keşfi**: Kategorilere göre filtreleyip ürünleri inceleyin
4. **Sepet**: Ürünleri sepete ekleyin ve miktarları ayarlayın
5. **Sipariş**: Güvenli ödeme ile alışverişinizi tamamlayın
6. **Takip**: Sipariş durumunuzu dashboard'dan takip edin

### Admin İşlemleri
1. **Admin Paneli**: `/admin` adresinden yönetici girişi yapın
2. **Ürün Yönetimi**: Yeni ürün ekleyin, fiyat ve stok güncelleyin
3. **Sipariş Kontrolü**: Müşteri siparişlerini görüntüleyin ve yönetin
4. **Kampanya Oluşturma**: İndirim kuponları ve kampanyalar düzenleyin
5. **Analitik**: Satış raporlarını ve istatistikleri görüntüleyin

## 🌟 Öne Çıkan Özellikler

- **📱 Mobil Uyumlu**: Responsive tasarım ile her cihazda mükemmel deneyim
- **🔍 Akıllı Filtreleme**: Kategori ve fiyat filtresi ile kolay ürün bulma
- **💫 Smooth UX**: Animasyonlu geçişler ve kullanıcı dostu arayüz
- **🔐 Güvenli Sistem**: Session tabanlı kimlik doğrulama
- **📊 Admin Dashboard**: Kapsamlı yönetim paneli
- **🎯 Kampanya Sistemi**: Dinamik kupon ve indirim sistemi
- **💬 Müşteri Yorumları**: Sosyal kanıt ve güven oluşturma
- **📧 Newsletter**: E-posta bülteni sistemi

## 🤝 Katkıda Bulunma

1. Bu repository'yi fork edin
2. Feature branch'i oluşturun (`git checkout -b yeni-ozellik`)
3. Değişikliklerinizi commit edin (`git commit -m 'Yeni özellik eklendi'`)
4. Branch'inizi push edin (`git push origin yeni-ozellik`)
5. Pull Request oluşturun

## 📝 Lisans

Bu proje MIT lisansı altında lisanslanmıştır.

## 📞 İletişim

- **E-posta**: info@organikurunler.com
- **Telefon**: +90 555 555 55 55
- **Adres**: Çiftlik Mahallesi, Organik Sokak No:1

---

⭐ Projeyi beğendiyseniz star vermeyi unutmayın!