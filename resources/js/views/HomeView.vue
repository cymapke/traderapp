<template>
    <div class="homepage-container">
        <!-- Header Section -->
        <header class="header">
            <div class="header-content">
                <!-- Logo -->
                <div class="logo">
                    TraderApp
                </div>

                <!-- Navigation Buttons -->
                <div class="nav-buttons">
                    <router-link :to="{ name: 'login' }" class="login-button">
                        Log in
                    </router-link>
                    <router-link :to="{ name: 'register' }" class="signup-button">
                        Sign up
                    </router-link>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-wrapper">
                <!-- Left Column - Text Content -->
                <div class="text-content">
                    <!-- Main Heading -->
                    <h1 class="main-heading">
                        Integrity in investment
                    </h1>

                    <!-- Description -->
                    <p class="description">
                        Join millions in trading with TraderApp,<br>
                        the top platform for realtime trading.
                    </p>
                </div>

                <!-- Right Column - Banner Image -->
                <div class="banner-section">                 
                    <!-- Banner Image -->
                    <div class="banner-container">
                        <img 
                            src="/images/home_banner.jpg" 
                            alt="TraderApp Platform Preview"
                            class="banner-image"
                        />
                        <!-- Decorative border overlay -->
                        <div class="banner-border"></div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="footer">
            <div class="footer-content">
                <p class="copyright">
                    © {{ currentYear }} TraderApp. All rights reserved.
                </p>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const currentYear = ref(new Date().getFullYear());
</script>

<style scoped>
/* Container Styles */
.homepage-container {
    min-height: 100vh;
    background-color: white;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    display: flex;
    flex-direction: column;
}

/* Header Styles */
.header {
    padding: 24px 40px;
    border-bottom: 1px solid #e5e7eb;
    background: white;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.header-content {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo {
    font-size: 24px;
    font-weight: 800;
    color: #482e92; /* Fiorentina purple */
    letter-spacing: -0.5px;
    font-family: 'Inter', system-ui, sans-serif;
    text-shadow: 0 1px 2px rgba(72, 46, 146, 0.1);
}

.nav-buttons {
    display: flex;
    gap: 16px;
}

.login-button {
    padding: 10px 24px;
    color: #4b5563;
    background-color: #f5f3ff; /* Slightly lighter lavender */
    border: 1px solid #e5e7eb;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.login-button:hover {
    background-color: #ede9fe;
    border-color: #482e92;
    color: #482e92;
}

.signup-button {
    padding: 10px 24px;
    background: linear-gradient(135deg, #482e92 0%, #5b3ea8 100%);
    color: white;
    border: none;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    border-radius: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(72, 46, 146, 0.2);
}

.signup-button:hover {
    background: linear-gradient(135deg, #5b3ea8 0%, #6d4bc4 100%);
    box-shadow: 0 4px 8px rgba(72, 46, 146, 0.3);
    transform: translateY(-1px);
}

/* Main Content Styles */
.main-content {
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
    padding: 80px 40px;
    flex: 1;
    display: flex;
    align-items: center;
}

.content-wrapper {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 80px;
    align-items: center;
}

/* Text Content Styles */
.text-content {
    padding-right: 40px;
}

.main-heading {
    font-size: 48px;
    font-weight: 800;
    line-height: 1.1;
    color: #111827;
    margin-bottom: 24px;
    background: linear-gradient(90deg, #482e92 0%, #6d4bc4 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.description {
    font-size: 20px;
    line-height: 1.7;
    color: #4b5563;
    margin-bottom: 40px;
    font-weight: 400;
}

/* Banner Section Styles */
.banner-section {
    position: relative;
}

.banner-container {
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 
        0 10px 25px -5px rgba(72, 46, 146, 0.1),
        0 20px 40px -10px rgba(72, 46, 146, 0.15),
        0 0 0 1px rgba(72, 46, 146, 0.05);
    position: relative;
    transition: all 0.4s ease;
}

.banner-container:hover {
    transform: translateY(-4px);
    box-shadow: 
        0 20px 40px -10px rgba(72, 46, 146, 0.2),
        0 30px 60px -15px rgba(72, 46, 146, 0.25),
        0 0 0 1px rgba(72, 46, 146, 0.1);
}

.banner-image {
    width: 100%;
    height: auto;
    display: block;
    max-height: 500px;
    object-fit: cover;
}

.banner-border {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border: 3px solid transparent;
    border-radius: 16px;
    background: linear-gradient(135deg, #482e92, #5b3ea8, #6d4bc4) border-box;
    -webkit-mask: 
        linear-gradient(#fff 0 0) padding-box, 
        linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    pointer-events: none;
}

/* Footer Styles */
.footer {
    background: #f9fafb;
    padding: 24px 40px;
    border-top: 1px solid #e5e7eb;
}

.footer-content {
    max-width: 1200px;
    margin: 0 auto;
    text-align: center;
}

.copyright {
    color: #6b7280;
    font-size: 14px;
    font-weight: 500;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .content-wrapper {
        grid-template-columns: 1fr;
        gap: 60px;
    }
    
    .text-content {
        padding-right: 0;
        text-align: center;
    }
    
    .main-heading {
        font-size: 40px;
    }
}

@media (max-width: 768px) {
    .header {
        padding: 20px 24px;
    }
    
    .main-content {
        padding: 60px 24px;
    }
    
    .main-heading {
        font-size: 32px;
    }
    
    .description {
        font-size: 18px;
    }
    
    .nav-buttons {
        gap: 12px;
    }
    
    .login-button,
    .signup-button {
        padding: 8px 20px;
        font-size: 13px;
    }
}

@media (max-width: 480px) {
    .header {
        padding: 16px 20px;
    }
    
    .main-content {
        padding: 40px 20px;
    }
    
    .logo {
        font-size: 20px;
    }
    
    .nav-buttons {
        flex-direction: column;
        gap: 8px;
    }
    
    .login-button,
    .signup-button {
        width: 100px;
        padding: 6px 16px;
    }
}
</style>
