<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <span class="hero-tagline">Egyptian Local Quality</span>
        <h1 class="hero-title">Elevate Your Daily Wear</h1>
        <p class="hero-description">Discover our carefully crafted collection of premium ringer t-shirts, knitted polos, and everyday staples made from fine local cotton.</p>
        <div class="hero-actions">
            <a href="<?= $base ?>/products" class="btn btn-primary">Shop Collection</a>
            <a href="#categories" class="btn btn-outline-white">Explore Categories</a>
        </div>
    </div>
</section>

<!-- Brand Values -->
<section class="values-section">
    <div class="container">
        <div class="values-grid">
            <div class="value-card">
                <div class="value-icon">
                    <svg viewBox="0 0 24 24" width="30" height="30" stroke="currentColor" stroke-width="1.5" fill="none">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
                    </svg>
                </div>
                <h3>100% Egyptian Cotton</h3>
                <p>Breathable, extra-soft fibers grown locally and spun to endure everyday wear.</p>
            </div>
            
            <div class="value-card">
                <div class="value-icon">
                    <svg viewBox="0 0 24 24" width="30" height="30" stroke="currentColor" stroke-width="1.5" fill="none">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M8 14s1.5 2 4 2 4-2 4-2M9 9h.01M15 9h.01"></path>
                    </svg>
                </div>
                <h3>Tailored Modern Fit</h3>
                <p>Designed with meticulous cuts resembling concrete shapes that flatter and move with you.</p>
            </div>
            
            <div class="value-card">
                <div class="value-icon">
                    <svg viewBox="0 0 24 24" width="30" height="30" stroke="currentColor" stroke-width="1.5" fill="none">
                        <rect x="1" y="3" width="15" height="13"></rect>
                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                        <circle cx="5.5" cy="18.5" r="2.5"></circle>
                        <circle cx="18.5" cy="18.5" r="2.5"></circle>
                    </svg>
                </div>
                <h3>Fast Local Shipping</h3>
                <p>Quick delivery right to your doorstep across Cairo, Giza, and all Egyptian governorates.</p>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section id="categories" class="categories-section">
    <div class="container">
        <h2 class="section-title">Shop by Category</h2>
        <div class="category-grid">
            
            <!-- Category 1: T-Shirts -->
            <a href="<?= $base ?>/products?category=t-shirts" class="category-card">
                <div class="category-bg" style="background-color: #12104a;"></div>
                <div class="category-overlay"></div>
                <div class="category-info">
                    <h3>T-Shirts</h3>
                    <span class="shop-link">Explore Collection &rarr;</span>
                </div>
            </a>

            <!-- Category 2: Ringer T-Shirts -->
            <a href="<?= $base ?>/products?category=ringer-t-shirts" class="category-card">
                <div class="category-bg" style="background-color: #1a1768;"></div>
                <div class="category-overlay"></div>
                <div class="category-info">
                    <h3>Ringer T-Shirts</h3>
                    <span class="shop-link">Explore Collection &rarr;</span>
                </div>
            </a>

            <!-- Category 3: Knitted Polos -->
            <a href="<?= $base ?>/products?category=knitted-polos" class="category-card">
                <div class="category-bg" style="background-color: #0b0933;"></div>
                <div class="category-overlay"></div>
                <div class="category-info">
                    <h3>Knitted Polos</h3>
                    <span class="shop-link">Explore Collection &rarr;</span>
                </div>
            </a>

            <!-- Category 4: Tops -->
            <a href="<?= $base ?>/products?category=tops" class="category-card">
                <div class="category-bg" style="background-color: #1d1973;"></div>
                <div class="category-overlay"></div>
                <div class="category-info">
                    <h3>Tops</h3>
                    <span class="shop-link">Explore Collection &rarr;</span>
                </div>
            </a>
            
        </div>
    </div>
</section>
