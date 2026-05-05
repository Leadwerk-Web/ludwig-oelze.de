/* ============================================
   LUDWIG OELZE - MAIN JAVASCRIPT
   Premium Website Interactions & Animations
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {
    // #region agent log
    (function agentDbgPassport(phase) {
        try {
            var href = (typeof location !== 'undefined' && location.href) ? location.href : '';
            if (href.indexOf('passport') === -1 && (location.pathname || '').indexOf('passport') === -1) return;
            var mainEl = document.querySelector('main');
            var reveals = document.querySelectorAll('.reveal');
            var vis = document.querySelectorAll('.reveal.visible');
            fetch('http://127.0.0.1:7413/ingest/4726b580-09e3-4524-8bf3-0571de1cda91', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-Debug-Session-Id': '5e6292' }, body: JSON.stringify({ sessionId: '5e6292', runId: 'pre', hypothesisId: phase === 'start' ? 'H3-H4' : 'H3', location: 'main.js:DOMContentLoaded', message: 'passport page: ' + phase, data: { phase: phase, pathname: location.pathname, htmlLang: document.documentElement.getAttribute('lang'), bodyClass: document.body.className, mainTextLen: mainEl ? (mainEl.innerText || '').length : 0, revealCount: reveals.length, revealVisibleCount: vis.length }, timestamp: Date.now() }) }).catch(function () {});
        } catch (e) {}
    })('start');
    // #endregion
    // Initialize all components
    initHeader();
    initMobileNav();
    initScrollReveal();
    initAccordion();
    initTabs();
    initStickyMobileCTA();
    initSmoothScroll();
    initParallax();
    initTestimonialSlider();
    initPartnerCarousel();
    initFormValidation();
    initLazyLoading();
    initHeroMandantenStand();
    initDurchblickCalculator();
    initCustomerVideoShowcase();
    // #region agent log
    (function agentDbgCtaMobileWidth() {
        function logCtaMobile() {
            try {
                var wrap = document.querySelector('.cta-section .cta-actions');
                if (!wrap) return;
                var vw = window.innerWidth || 0;
                if (vw > 767) return;
                var btns = wrap.querySelectorAll('.btn');
                if (!btns.length) return;
                var b0 = btns[0];
                var cs0 = getComputedStyle(b0);
                var csw = getComputedStyle(wrap);
                var rw = Math.round(wrap.getBoundingClientRect().width);
                var bw0 = Math.round(b0.getBoundingClientRect().width);
                var btnList = [];
                btns.forEach(function (b, i) {
                    btnList.push({ i: i, w: Math.round(b.getBoundingClientRect().width) });
                });
                fetch('http://127.0.0.1:7413/ingest/4726b580-09e3-4524-8bf3-0571de1cda91', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-Debug-Session-Id': 'e7c6a9' }, body: JSON.stringify({ sessionId: 'e7c6a9', runId: 'cta-pre', hypothesisId: 'H1-H2', location: 'main.js:agentDbgCtaMobileWidth', message: 'cta-actions mobile layout', data: { vw: vw, wrapInnerW: rw, firstBtnW: bw0, matchFullWidth: bw0 >= rw - 2, btnComputedWidth: cs0.width, btnComputedMaxW: cs0.maxWidth, wrapAlignItems: csw.alignItems, wrapFlexDir: csw.flexDirection, btnWidths: btnList }, timestamp: Date.now() }) }).catch(function () {});
            } catch (e) {}
        }
        requestAnimationFrame(function () {
            requestAnimationFrame(logCtaMobile);
        });
    })();
    // #endregion
    // #region agent log
    window.addEventListener('load', function () {
        try {
            var href = (typeof location !== 'undefined' && location.href) ? location.href : '';
            if (href.indexOf('passport') === -1 && (location.pathname || '').indexOf('passport') === -1) return;
            var mainEl = document.querySelector('main');
            var vis = document.querySelectorAll('.reveal.visible');
            fetch('http://127.0.0.1:7413/ingest/4726b580-09e3-4524-8bf3-0571de1cda91', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-Debug-Session-Id': '5e6292' }, body: JSON.stringify({ sessionId: '5e6292', runId: 'pre', hypothesisId: 'H3', location: 'main.js:load', message: 'passport after load', data: { pathname: location.pathname, htmlLang: document.documentElement.getAttribute('lang'), mainTextLen: mainEl ? (mainEl.innerText || '').length : 0, revealVisibleCount: vis.length }, timestamp: Date.now() }) }).catch(function () {});
        } catch (e) {}
    }, { once: true });
    // #endregion
});

/* ============================================
   1. HEADER SCROLL BEHAVIOR
   ============================================ */
function initHeader() {
    const header = document.querySelector('.header');
    if (!header) return;
    
    let lastScroll = 0;
    const scrollThreshold = 100;
    const forceScrolled = header.dataset.headerState === 'scrolled' || document.body.classList.contains('header-scrolled');

    if (forceScrolled) {
        header.classList.add('scrolled');
        return;
    }
    
    const updateHeaderState = () => {
        const currentScroll = window.pageYOffset;
        
        // Add scrolled class for background
        if (currentScroll > scrollThreshold) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
        
        // Hide/show on scroll (optional - uncomment if needed)
        // if (currentScroll > lastScroll && currentScroll > 300) {
        //     header.style.transform = 'translateY(-100%)';
        // } else {
        //     header.style.transform = 'translateY(0)';
        // }
        
        lastScroll = currentScroll;
    };

    updateHeaderState();
    window.addEventListener('scroll', updateHeaderState);
}

/* ============================================
   2. MOBILE NAVIGATION
   ============================================ */
function initMobileNav() {
    const menuToggle = document.querySelector('.menu-toggle');
    const mobileNav = document.querySelector('.nav-mobile');
    const mobileLinks = document.querySelectorAll('.nav-mobile a:not(.dropdown-toggle)');
    const body = document.body;
    
    if (!menuToggle || !mobileNav) return;
    
    // Toggle menu
    menuToggle.addEventListener('click', () => {
        menuToggle.classList.toggle('active');
        mobileNav.classList.toggle('active');
        body.classList.toggle('overflow-hidden');
    });
    
    // Close menu on link click (but not dropdown toggles)
    mobileLinks.forEach(link => {
        link.addEventListener('click', () => {
            menuToggle.classList.remove('active');
            mobileNav.classList.remove('active');
            body.classList.remove('overflow-hidden');
        });
    });
    
    // Mobile dropdown toggles
    const dropdownToggles = document.querySelectorAll('.nav-mobile .has-dropdown > button, .nav-mobile .has-dropdown > a.dropdown-toggle');
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', (e) => {
            if (toggle.tagName.toLowerCase() === 'a') {
                e.preventDefault();
            }
            const parent = toggle.closest('.has-dropdown');
            if (!parent) return;
            const isOpen = parent.classList.toggle('open');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    });
    
    // Close menu on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && mobileNav.classList.contains('active')) {
            menuToggle.classList.remove('active');
            mobileNav.classList.remove('active');
            body.classList.remove('overflow-hidden');
        }
    });
}

/* ============================================
   3. SCROLL REVEAL ANIMATIONS
   ============================================ */
function initScrollReveal() {
    const reveals = document.querySelectorAll('.reveal');
    
    if (reveals.length === 0) return;
    
    // Optimierte Einstellungen für smoothere Animationen
    const observerOptions = {
        root: null,
        rootMargin: '0px 0px -80px 0px',
        threshold: 0.15
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Kleine Verzögerung für smootheren Effekt
                requestAnimationFrame(() => {
                    entry.target.classList.add('visible');
                });
            }
        });
    }, observerOptions);
    
    reveals.forEach(reveal => {
        observer.observe(reveal);
    });
}

/* ============================================
   4. ACCORDION / FAQ
   ============================================ */
function initAccordion() {
    const accordions = document.querySelectorAll('.accordion');
    
    accordions.forEach(accordion => {
        const items = accordion.querySelectorAll('.accordion-item');
        
        items.forEach(item => {
            const header = item.querySelector('.accordion-header');
            const content = item.querySelector('.accordion-content');
            
            if (!header || !content) return;
            
            header.addEventListener('click', () => {
                const isActive = item.classList.contains('active');
                
                // Close all other items (optional - for single open accordion)
                // items.forEach(otherItem => {
                //     otherItem.classList.remove('active');
                // });
                
                // Toggle current item
                if (isActive) {
                    item.classList.remove('active');
                    content.style.maxHeight = '0';
                } else {
                    item.classList.add('active');
                    content.style.maxHeight = content.scrollHeight + 'px';
                }
            });
            
            // Keyboard accessibility
            header.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    header.click();
                }
            });
        });
    });
}

/* ============================================
   5. TABS
   ============================================ */
function initTabs() {
    const tabContainers = document.querySelectorAll('.tabs');
    
    tabContainers.forEach(container => {
        const buttons = container.querySelectorAll('.tab-button');
        const panels = container.querySelectorAll('.tab-panel');
        
        buttons.forEach((button, index) => {
            button.addEventListener('click', () => {
                // Remove active class from all
                buttons.forEach(btn => btn.classList.remove('active'));
                panels.forEach(panel => panel.classList.remove('active'));
                
                // Add active class to clicked
                button.classList.add('active');
                panels[index]?.classList.add('active');
            });
            
            // Keyboard navigation
            button.addEventListener('keydown', (e) => {
                let newIndex = index;
                
                if (e.key === 'ArrowRight') {
                    newIndex = (index + 1) % buttons.length;
                } else if (e.key === 'ArrowLeft') {
                    newIndex = (index - 1 + buttons.length) % buttons.length;
                }
                
                if (newIndex !== index) {
                    buttons[newIndex].focus();
                    buttons[newIndex].click();
                }
            });
        });
    });
}

/* ============================================
   6. STICKY MOBILE CTA
   ============================================ */
function initStickyMobileCTA() {
    const stickyCTA = document.querySelector('.sticky-cta');
    
    if (!stickyCTA) return;
    
    const showThreshold = 300;
    const footer = document.querySelector('.footer');
    
    window.addEventListener('scroll', () => {
        const currentScroll = window.pageYOffset;
        const footerTop = footer ? footer.offsetTop : document.body.scrollHeight;
        const windowHeight = window.innerHeight;
        
        // Show after threshold and hide near footer
        if (currentScroll > showThreshold && currentScroll + windowHeight < footerTop - 100) {
            stickyCTA.classList.add('visible');
        } else {
            stickyCTA.classList.remove('visible');
        }
    });
}

/* ============================================
   7. SMOOTH SCROLL FOR ANCHOR LINKS
   ============================================ */
function initSmoothScroll() {
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    
    anchorLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            const targetId = link.getAttribute('href');
            
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                e.preventDefault();
                
                const headerHeight = document.querySelector('.header')?.offsetHeight || 0;
                const targetPosition = targetElement.offsetTop - headerHeight - 20;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
}

/* ============================================
   8. PARALLAX EFFECT (HERO SECTIONS)
   ============================================ */
function initParallax() {
    const parallaxElements = document.querySelectorAll('.hero-bg');
    
    if (parallaxElements.length === 0) return;
    
    // Check for reduced motion preference
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }
    
    let ticking = false;
    
    window.addEventListener('scroll', () => {
        if (!ticking) {
            window.requestAnimationFrame(() => {
                parallaxElements.forEach(element => {
                    const scrolled = window.pageYOffset;
                    const rate = scrolled * 0.3;
                    element.style.transform = `translateY(${rate}px)`;
                });
                ticking = false;
            });
            ticking = true;
        }
    });
}

/* ============================================
   9. TESTIMONIAL SLIDER
   ============================================ */
function initTestimonialSlider() {
    const sliders = document.querySelectorAll('.testimonial-slider');
    
    sliders.forEach(slider => {
        const slides = slider.querySelectorAll('.testimonial');
        const dotsContainer = slider.querySelector('.slider-dots');
        let currentIndex = 0;
        let autoplayInterval;
        
        if (slides.length <= 1) return;
        
        // Create dots
        if (dotsContainer) {
            slides.forEach((_, index) => {
                const dot = document.createElement('button');
                dot.classList.add('slider-dot');
                if (index === 0) dot.classList.add('active');
                dot.setAttribute('aria-label', `Slide ${index + 1}`);
                dot.addEventListener('click', () => goToSlide(index));
                dotsContainer.appendChild(dot);
            });
        }
        
        const dots = dotsContainer?.querySelectorAll('.slider-dot');
        
        function goToSlide(index) {
            slides[currentIndex].classList.remove('active');
            dots?.[currentIndex]?.classList.remove('active');
            
            currentIndex = index;
            
            slides[currentIndex].classList.add('active');
            dots?.[currentIndex]?.classList.add('active');
        }
        
        function nextSlide() {
            const next = (currentIndex + 1) % slides.length;
            goToSlide(next);
        }
        
        function startAutoplay() {
            autoplayInterval = setInterval(nextSlide, 5000);
        }
        
        function stopAutoplay() {
            clearInterval(autoplayInterval);
        }
        
        // Start autoplay
        startAutoplay();
        
        // Pause on hover
        slider.addEventListener('mouseenter', stopAutoplay);
        slider.addEventListener('mouseleave', startAutoplay);
        
        // Touch swipe support
        let touchStartX = 0;
        let touchEndX = 0;
        
        slider.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        });
        
        slider.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        });
        
        function handleSwipe() {
            const swipeThreshold = 50;
            const diff = touchStartX - touchEndX;
            
            if (Math.abs(diff) > swipeThreshold) {
                if (diff > 0) {
                    // Swipe left - next slide
                    nextSlide();
                } else {
                    // Swipe right - previous slide
                    const prev = (currentIndex - 1 + slides.length) % slides.length;
                    goToSlide(prev);
                }
            }
        }
    });
}

/* ============================================
   10. PARTNER LOGO CAROUSEL
   ============================================ */
function initPartnerCarousel() {
    const carousels = document.querySelectorAll('.partner-carousel');
    
    carousels.forEach(carousel => {
        const track = carousel.querySelector('.carousel-track');
        if (!track) return;
        
        // Clone items for infinite scroll
        const items = track.querySelectorAll('.carousel-item');
        items.forEach(item => {
            const clone = item.cloneNode(true);
            track.appendChild(clone);
        });
        
        // Animation is handled via CSS
        // This ensures smooth infinite scrolling
    });
}

/* ============================================
   11. FORM VALIDATION
   ============================================ */
function initFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', (e) => {
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                // Remove previous error state
                field.classList.remove('error');
                const errorMsg = field.parentNode.querySelector('.error-message');
                if (errorMsg) errorMsg.remove();
                
                // Check validity
                if (!field.value.trim()) {
                    isValid = false;
                    showError(field, 'Dieses Feld ist erforderlich.');
                } else if (field.type === 'email' && !isValidEmail(field.value)) {
                    isValid = false;
                    showError(field, 'Bitte geben Sie eine gültige E-Mail-Adresse ein.');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
            }
        });
        
        // Real-time validation
        form.querySelectorAll('input, textarea').forEach(field => {
            field.addEventListener('blur', () => {
                validateField(field);
            });
            
            field.addEventListener('input', () => {
                if (field.classList.contains('error')) {
                    validateField(field);
                }
            });
        });
    });
    
    function showError(field, message) {
        field.classList.add('error');
        const errorElement = document.createElement('span');
        errorElement.classList.add('error-message');
        errorElement.textContent = message;
        field.parentNode.appendChild(errorElement);
    }
    
    function validateField(field) {
        field.classList.remove('error');
        const errorMsg = field.parentNode.querySelector('.error-message');
        if (errorMsg) errorMsg.remove();
        
        if (field.hasAttribute('required') && !field.value.trim()) {
            showError(field, 'Dieses Feld ist erforderlich.');
        } else if (field.type === 'email' && field.value && !isValidEmail(field.value)) {
            showError(field, 'Bitte geben Sie eine gültige E-Mail-Adresse ein.');
        }
    }
    
    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
}

/* ============================================
   12. LAZY LOADING FOR IMAGES
   ============================================ */
function initLazyLoading() {
    const lazyImages = document.querySelectorAll('img[data-src]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    
                    if (img.dataset.srcset) {
                        img.srcset = img.dataset.srcset;
                    }
                    
                    img.classList.add('loaded');
                    imageObserver.unobserve(img);
                }
            });
        }, {
            rootMargin: '50px 0px'
        });
        
        lazyImages.forEach(img => {
            imageObserver.observe(img);
        });
    } else {
        // Fallback for older browsers
        lazyImages.forEach(img => {
            img.src = img.dataset.src;
            if (img.dataset.srcset) {
                img.srcset = img.dataset.srcset;
            }
        });
    }
}

function initHeroMandantenStand() {
    const standElements = document.querySelectorAll('[data-current-month-stand]');
    if (!standElements.length) return;

    const now = new Date();
    const monthYear = new Intl.DateTimeFormat('de-DE', {
        month: 'long',
        year: 'numeric'
    }).format(now);
    const isoMonth = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;

    standElements.forEach((element) => {
        const prefix = element.dataset.currentMonthPrefix || 'Stand';
        element.textContent = `${prefix} ${monthYear}`;

        if (element.tagName.toLowerCase() === 'time') {
            element.setAttribute('datetime', isoMonth);
        }
    });
}

/* ============================================
   13. UTILITY FUNCTIONS
   ============================================ */

// Debounce function
function debounce(func, wait = 100) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

// Throttle function
function throttle(func, limit = 100) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Check if element is in viewport
function isInViewport(element) {
    const rect = element.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}

// Format phone number for tel: links
function formatPhoneNumber(phone) {
    return phone.replace(/\s/g, '').replace(/[^0-9+]/g, '');
}

/* ============================================
   14. CALENDLY INTEGRATION
   ============================================ */
function openCalendly(url) {
    if (typeof Calendly !== 'undefined') {
        Calendly.initPopupWidget({url: url});
    } else {
        window.open(url, '_blank');
    }
}

// Expose to global scope for onclick handlers
window.openCalendly = openCalendly;

/* ============================================
   15. COUNTER ANIMATION
   ============================================ */
function initCounters() {
    const counters = document.querySelectorAll('[data-counter]');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const target = parseInt(counter.dataset.counter);
                const duration = parseInt(counter.dataset.duration) || 2000;
                
                animateCounter(counter, target, duration);
                observer.unobserve(counter);
            }
        });
    }, { threshold: 0.5 });
    
    counters.forEach(counter => observer.observe(counter));
}

function animateCounter(element, target, duration) {
    let start = 0;
    const startTime = performance.now();
    
    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        // Easing function (ease-out)
        const easeOut = 1 - Math.pow(1 - progress, 3);
        const current = Math.floor(easeOut * target);
        
        element.textContent = current.toLocaleString('de-DE');
        
        if (progress < 1) {
            requestAnimationFrame(update);
        } else {
            element.textContent = target.toLocaleString('de-DE');
        }
    }
    
    requestAnimationFrame(update);
}

/* ============================================
   16. COOKIE CONSENT
   ============================================ */
function initCookieConsent() {
    const banner = document.querySelector('.cookie-banner');
    const acceptBtn = document.querySelector('.cookie-accept');
    const declineBtn = document.querySelector('.cookie-decline');
    
    if (!banner) return;
    
    // Check if already consented
    if (localStorage.getItem('cookieConsent')) {
        banner.style.display = 'none';
        return;
    }
    
    // Show banner
    setTimeout(() => {
        banner.classList.add('visible');
    }, 1000);
    
    acceptBtn?.addEventListener('click', () => {
        localStorage.setItem('cookieConsent', 'accepted');
        banner.classList.remove('visible');
        // Initialize analytics here if needed
    });
    
    declineBtn?.addEventListener('click', () => {
        localStorage.setItem('cookieConsent', 'declined');
        banner.classList.remove('visible');
    });
}

/* ============================================
   17. SCROLL PROGRESS BAR
   ============================================ */
function initScrollProgress() {
    const progressBar = document.querySelector('.scroll-progress');
    
    if (!progressBar) return;
    
    window.addEventListener('scroll', throttle(() => {
        const scrollTop = window.pageYOffset;
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
        const progress = (scrollTop / docHeight) * 100;
        
        progressBar.style.width = `${progress}%`;
    }, 10));
}

/* ============================================
   18. INITIALIZE ADDITIONAL FEATURES
   ============================================ */
// Call additional initializers
document.addEventListener('DOMContentLoaded', function() {
    initCounters();
    initCookieConsent();
    initScrollProgress();
});

/* ============================================
   19. WHATSAPP FLOATING BUTTON
   ============================================ */
function initWhatsAppButton() {
    const whatsappBtn = document.querySelector('.whatsapp-float');
    
    if (!whatsappBtn) return;
    
    // Show after scroll
    window.addEventListener('scroll', debounce(() => {
        if (window.pageYOffset > 500) {
            whatsappBtn.classList.add('visible');
        } else {
            whatsappBtn.classList.remove('visible');
        }
    }, 100));
}

// Initialize WhatsApp button
document.addEventListener('DOMContentLoaded', initWhatsAppButton);

/* ============================================
   20. Durchblick Calculator
   ============================================ */
function initDurchblickCalculator() {
    const calculators = document.querySelectorAll('[data-durchblick-calculator]');

    calculators.forEach((calculator) => {
        const monthlyInput = calculator.querySelector('[data-durchblick-monthly]');
        const yearsInput = calculator.querySelector('[data-durchblick-years]');
        const advantageOutput = calculator.querySelector('[data-durchblick-advantage]');
        const comparisonOutput = calculator.querySelector('[data-durchblick-comparison]');
        const shareButton = calculator.querySelector('[data-durchblick-share]');
        const annualReturn = Number.parseFloat(calculator.dataset.annualReturn || '0.07');
        const traditionalFee = Number.parseFloat(calculator.dataset.traditionalFee || '0.05');
        const formatter = new Intl.NumberFormat('de-DE', {
            style: 'currency',
            currency: 'EUR',
            maximumFractionDigits: 0
        });

        if (!monthlyInput || !yearsInput || !advantageOutput || !comparisonOutput) {
            return;
        }

        const calculateFutureValue = (monthlyAmount, years, fee = 0) => {
            const yearlyContribution = monthlyAmount * 12 * (1 - fee);
            return yearlyContribution * ((Math.pow(1 + annualReturn, years) - 1) / annualReturn);
        };

        const update = () => {
            const monthlyAmount = Math.max(Number.parseFloat(monthlyInput.value) || 0, 0);
            const years = Math.max(Number.parseInt(yearsInput.value, 10) || 0, 0);
            const totalInvested = monthlyAmount * 12 * years;
            const withoutFees = calculateFutureValue(monthlyAmount, years, 0);
            const withFees = calculateFutureValue(monthlyAmount, years, traditionalFee);
            const difference = Math.max(withoutFees - withFees, 0);

            advantageOutput.textContent = `Vorteil: ${formatter.format(difference)}`;
            comparisonOutput.innerHTML = [
                `<p><span>Einzahlung</span><strong>${formatter.format(totalInvested)}</strong></p>`,
                `<p><span>Ohne modellhafte Abschlusskosten</span><strong>${formatter.format(withoutFees)}</strong></p>`,
                `<p><span>Mit 5% modellhaften Abschlusskosten</span><strong>${formatter.format(withFees)}</strong></p>`
            ].join('');
        };

        calculator.addEventListener('submit', (event) => {
            event.preventDefault();
            update();
        });

        [monthlyInput, yearsInput].forEach((input) => {
            input.addEventListener('input', update);
        });

        shareButton?.addEventListener('click', async () => {
            const url = `${window.location.origin}${window.location.pathname}`;
            const text = `Durchblick: ${url}`;

            if (navigator.share) {
                try {
                    await navigator.share({
                        title: 'Durchblick',
                        text,
                        url
                    });
                    return;
                } catch (error) {
                    // Fall back to clipboard if native sharing is cancelled or unavailable.
                }
            }

            try {
                await navigator.clipboard.writeText(text);
                shareButton.textContent = 'Link kopiert';
                window.setTimeout(() => {
                    shareButton.textContent = 'Jetzt teilen';
                }, 1800);
            } catch (error) {
                window.location.href = `mailto:?subject=Durchblick&body=${encodeURIComponent(text)}`;
            }
        });

        update();
    });
}

/* ============================================
   21. CUSTOMER VIDEO SHOWCASE
   ============================================ */
function initCustomerVideoShowcase() {
    const sections = document.querySelectorAll('.customer-videos-section');

    sections.forEach((section) => {
        const cards = Array.from(section.querySelectorAll('.customer-video-card'));
        const modal = section.querySelector('.customer-video-modal');
        const modalPlayer = modal?.querySelector('.customer-video-modal-player');
        const modalTitle = modal?.querySelector('.customer-video-modal-title');
        const modalKicker = modal?.querySelector('.customer-video-kicker');
        const prevButton = modal?.querySelector('[data-video-prev]');
        const nextButton = modal?.querySelector('[data-video-next]');
        const body = document.body;
        let currentIndex = -1;

        const getCardData = (index) => {
            const card = cards[index];
            if (!card) {
                return null;
            }

            const inlineVideo = card.querySelector('.customer-video-inline');
            const source = inlineVideo?.currentSrc
                || inlineVideo?.querySelector('source')?.getAttribute('src')
                || card.getAttribute('data-video-src')
                || '';

            return {
                card,
                inlineVideo,
                source,
                title: card.querySelector('.customer-video-title')?.textContent?.trim() || 'Kundenvideo',
                kicker: card.querySelector('.customer-video-kicker')?.textContent?.trim() || 'Kundenvideo',
                poster: inlineVideo?.getAttribute('poster') || card.getAttribute('data-video-poster') || ''
            };
        };

        const tryPlayInline = (inlineVideo) => {
            if (!inlineVideo) {
                return;
            }

            inlineVideo.muted = true;
            inlineVideo.defaultMuted = true;
            inlineVideo.loop = true;
            inlineVideo.autoplay = true;
            inlineVideo.playsInline = true;

            const promise = inlineVideo.play();
            if (promise && typeof promise.catch === 'function') {
                promise.catch(() => {});
            }
        };

        const updateNavState = () => {
            if (!prevButton || !nextButton) {
                return;
            }

            const hasMultipleVideos = cards.length > 1;
            prevButton.hidden = !hasMultipleVideos || currentIndex <= 0;
            nextButton.hidden = !hasMultipleVideos || currentIndex >= cards.length - 1;
        };

        const pauseInlineVideos = () => {
            cards.forEach((card) => {
                card.querySelector('.customer-video-inline')?.pause();
            });
        };

        const resumeInlineVideos = () => {
            cards.forEach((card) => {
                tryPlayInline(card.querySelector('.customer-video-inline'));
            });
        };

        const showVideoAt = (index) => {
            const data = getCardData(index);
            if (!modal || !modalPlayer || !data || !data.inlineVideo || !data.source) {
                return;
            }

            currentIndex = index;
            pauseInlineVideos();

            modal.hidden = false;
            modal.setAttribute('aria-hidden', 'false');
            body.classList.add('overflow-hidden');

            if (modalTitle) {
                modalTitle.textContent = data.title;
            }

            if (modalKicker) {
                modalKicker.textContent = data.kicker;
            }

            modalPlayer.pause();
            modalPlayer.removeAttribute('src');
            while (modalPlayer.firstChild) {
                modalPlayer.removeChild(modalPlayer.firstChild);
            }

            const source = document.createElement('source');
            source.src = data.source;
            source.type = 'video/mp4';
            modalPlayer.appendChild(source);

            if (data.poster) {
                modalPlayer.setAttribute('poster', data.poster);
            } else {
                modalPlayer.removeAttribute('poster');
            }

            modalPlayer.currentTime = 0;
            modalPlayer.muted = false;
            modalPlayer.defaultMuted = false;
            modalPlayer.controls = true;
            modalPlayer.load();
            updateNavState();

            const playPromise = modalPlayer.play();
            if (playPromise && typeof playPromise.catch === 'function') {
                playPromise.catch(() => {});
            }
        };

        cards.forEach((card, index) => {
            const inlineVideo = card.querySelector('.customer-video-inline');
            const playButton = card.querySelector('[data-video-play]');

            if (inlineVideo) {
                if (inlineVideo.readyState >= 2) {
                    tryPlayInline(inlineVideo);
                } else {
                    inlineVideo.addEventListener('canplay', () => tryPlayInline(inlineVideo), { once: true });
                }
            }

            playButton?.addEventListener('click', () => showVideoAt(index));
            inlineVideo?.addEventListener('click', () => showVideoAt(index));
        });

        const closeModal = () => {
            if (!modal || !modalPlayer) return;

            modalPlayer.pause();
            modalPlayer.removeAttribute('src');
            while (modalPlayer.firstChild) {
                modalPlayer.removeChild(modalPlayer.firstChild);
            }
            modalPlayer.load();

            modal.hidden = true;
            modal.setAttribute('aria-hidden', 'true');
            body.classList.remove('overflow-hidden');
            resumeInlineVideos();
        };

        modal?.querySelectorAll('[data-video-close]').forEach((element) => {
            element.addEventListener('click', closeModal);
        });

        prevButton?.addEventListener('click', () => {
            if (currentIndex > 0) {
                showVideoAt(currentIndex - 1);
            }
        });

        nextButton?.addEventListener('click', () => {
            if (currentIndex < cards.length - 1) {
                showVideoAt(currentIndex + 1);
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && modal && !modal.hidden) {
                closeModal();
            }

            if (!modal || modal.hidden) {
                return;
            }

            if (event.key === 'ArrowLeft' && currentIndex > 0) {
                showVideoAt(currentIndex - 1);
            }

            if (event.key === 'ArrowRight' && currentIndex < cards.length - 1) {
                showVideoAt(currentIndex + 1);
            }
        });
    });
}
