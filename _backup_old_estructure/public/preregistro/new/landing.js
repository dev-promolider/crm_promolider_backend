// ========== SCROLL PROGRESS BAR ==========
const scrollProgress = document.getElementById('scrollProgress');
window.addEventListener('scroll', () => {
    const scrollTop = window.scrollY;
    const docHeight = document.documentElement.scrollHeight - window.innerHeight;
    const scrollPercent = (scrollTop / docHeight) * 100;
    scrollProgress.style.width = scrollPercent + '%';
});

// ========== SCROLL REVEAL ==========
const revealElements = document.querySelectorAll('.reveal, .reveal-left, .reveal-right');

const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('active');
        }
    });
}, {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
});

revealElements.forEach(el => revealObserver.observe(el));

// ========== SOCIAL PROOF - RECENT SIGNUPS ==========
const cities = [
    'Bogotá', 'Medellín', 'Lima', 'Quito', 'Santiago',
    'Buenos Aires', 'Caracas', 'Ciudad de México', 'Guayaquil',
    'Barranquilla', 'Cali', 'Monterrey', 'La Paz', 'Asunción'
];

const recentSignup = document.getElementById('recentSignup');

function updateRecentSignup() {
    const city = cities[Math.floor(Math.random() * cities.length)];
    const minutes = Math.floor(Math.random() * 5) + 1;
    recentSignup.textContent = `Alguien de ${city} se registró hace ${minutes} min`;
}

setInterval(updateRecentSignup, 8000);

// ========== COUNTER ANIMATION ==========
function animateCounter(element, target) {
    let current = 0;
    const increment = target / 60;
    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            current = target;
            clearInterval(timer);
        }
        element.textContent = Math.floor(current).toLocaleString();
    }, 16);
}

// Observe when counters come into view
const counterElements = document.querySelectorAll('.counter-number');
let countersAnimated = false;

const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting && !countersAnimated) {
            countersAnimated = true;
            counterElements.forEach(el => {
                const target = parseInt(el.textContent.replace(/,/g, ''));
                animateCounter(el, target);
            });
        }
    });
}, { threshold: 0.5 });

counterElements.forEach(el => counterObserver.observe(el));

// ========== TOAST NOTIFICATION ==========
function showToast(message, icon = 'fa-check-circle', bgColor = '') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast pointer-events-auto px-6 py-4 rounded-xl shadow-2xl font-semibold flex items-center gap-3 mb-3`;
    toast.style.background = bgColor || 'linear-gradient(135deg, #0BF50B 0%, #00a800 100%)';
    toast.style.color = bgColor ? '#ffffff' : '#0a0a0a';
    toast.innerHTML = `<i class="fa-solid ${icon}"></i> ${message}`;
    container.appendChild(toast);

    requestAnimationFrame(() => {
        toast.classList.add('show');
    });

    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 400);
    }, 5000);
}

// ========== FORM VALIDATION & SUBMISSION ==========
const formularios = document.querySelectorAll('.form-registro');

function validateEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function validatePhone(phone) {
    return /^[\+\d\s\-\(\)]{7,15}$/.test(phone);
}

function validateField(input) {
    const errorMsg = input.closest('div').parentElement.querySelector('.error-msg')
                  || input.closest('div').nextElementSibling;

    if (!input.value.trim()) {
        input.classList.add('error');
        if (errorMsg && errorMsg.classList.contains('error-msg')) errorMsg.classList.add('show');
        return false;
    }

    if (input.type === 'email' && !validateEmail(input.value)) {
        input.classList.add('error');
        if (errorMsg && errorMsg.classList.contains('error-msg')) errorMsg.classList.add('show');
        return false;
    }

    if (input.name === 'whatsapp' && !validatePhone(input.value)) {
        input.classList.add('error');
        if (errorMsg && errorMsg.classList.contains('error-msg')) {
            errorMsg.textContent = 'Por favor ingresa un número válido (+ código de país)';
            errorMsg.classList.add('show');
        }
        return false;
    }

    input.classList.remove('error');
    if (errorMsg && errorMsg.classList.contains('error-msg')) errorMsg.classList.remove('show');
    return true;
}

function persistPreregistroData(responseData, submittedForm) {
    const prefill = responseData.preregistro_prefill || {};
    const fieldMap = {
        usuario: 'preregistro_usuario',
        correo: 'preregistro_correo',
        nombre: 'preregistro_nombre',
        apellido: 'preregistro_apellido',
        telefono: 'preregistro_whatsapp',
        fecha_nacimiento: 'preregistro_fecha_nacimiento',
        tipo_documento: 'preregistro_tipo_documento',
        numero_documento: 'preregistro_numero_documento',
        pais: 'preregistro_pais',
        tipo_usuario: 'preregistro_tipo_usuario',
        tipo_cuenta: 'preregistro_tipo_cuenta',
        metodo_pago: 'preregistro_metodo_pago',
    };

    Object.values(fieldMap).forEach((storageKey) => sessionStorage.removeItem(storageKey));
    sessionStorage.setItem('preregistro_id', responseData.preregistro_id || prefill.preregistro_id || '');
    sessionStorage.setItem('preregistro_nombre', prefill.nombre || submittedForm.nombres || '');
    sessionStorage.setItem('preregistro_apellido', prefill.apellido || submittedForm.apellidos || '');
    sessionStorage.setItem('preregistro_correo', prefill.correo || submittedForm.correo || '');
    sessionStorage.setItem('preregistro_whatsapp', prefill.telefono || submittedForm.whatsapp || '');
    sessionStorage.setItem('preregistro_referidor', prefill.referidor || responseData.username || window.USERNAME || '');
    sessionStorage.setItem('preregistro_lado', prefill.lado || responseData.lado || window.LADO || '');
    sessionStorage.setItem('preregistro_pending_complete', prefill.reuse_pending_registration ? '1' : '0');

    Object.entries(fieldMap).forEach(([field, storageKey]) => {
        // Solo si NO fue establecido antes por submittedForm (datos nuevos tienen prioridad)
        if (prefill[field] !== undefined && prefill[field] !== null && !sessionStorage.getItem(storageKey)) {
            sessionStorage.setItem(storageKey, prefill[field]);
        }
    });
}

formularios.forEach(form => {
    // Real-time validation on blur
    form.querySelectorAll('input').forEach(input => {
        input.addEventListener('blur', () => validateField(input));
        input.addEventListener('input', () => {
            if (input.classList.contains('error')) {
                validateField(input);
            }
        });
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const inputs = this.querySelectorAll('input[required]');
        let isValid = true;

        inputs.forEach(input => {
            if (!validateField(input)) isValid = false;
        });

        if (!isValid) return;

        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;

        // Loading state
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Procesando...';
        btn.classList.add('opacity-80', 'cursor-not-allowed');
        btn.disabled = true;

        const nameInput = this.querySelector('input[name="nombre"]');
        const apellidoInput = this.querySelector('input[name="apellido"]');
        const emailInput = this.querySelector('input[name="email"]');
        const whatsappInput = this.querySelector('input[name="whatsapp"]');
        const payload = {
            nombres: nameInput.value.trim(),
            apellidos: apellidoInput.value.trim() || 'No indicado',
            correo: emailInput.value.trim(),
            whatsapp: whatsappInput.value.trim(),
            referidor: window.USERNAME || '',
            lado: window.LADO || null
        };

        fetch(window.location.pathname + window.location.search, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(payload)
        })
        .then(async response => {
            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                const errors = data.errors ? Object.values(data.errors).flat() : [];
                throw new Error(errors[0] || data.message || 'No pudimos completar el preregistro.');
            }

            btn.innerHTML = '<i class="fa-solid fa-check mr-2"></i> Reservado';
            btn.style.background = 'linear-gradient(135deg, #0BF50B 0%, #00a800 100%)';
            btn.style.color = '#0a0a0a';
            btn.style.boxShadow = '0 0 0 3px rgba(11, 245, 11, 0.3)';

            const counters = document.querySelectorAll('.counter-number');
            counters.forEach(c => {
                const current = parseInt(c.textContent.replace(/,/g, '')) || 0;
                c.textContent = (current + 1).toLocaleString();
            });

            persistPreregistroData(data, payload);
            showToast('Registro guardado. Te llevamos al siguiente paso.', 'fa-check-circle');

            if (data.redirect_url) {
                // Validar que la URL de redirección pertenezca al mismo origen
                try {
                    const url = new URL(data.redirect_url, window.location.origin);
                    if (url.origin === window.location.origin) {
                        window.location.href = data.redirect_url;
                    } else {
                        showToast('Redirección a sitio externo no permitida.', 'fa-triangle-exclamation', 'linear-gradient(135deg, #ef4444 0%, #991b1b 100%)');
                    }
                } catch {
                    showToast('URL de redirección inválida.', 'fa-triangle-exclamation', 'linear-gradient(135deg, #ef4444 0%, #991b1b 100%)');
                }
                return;
            }

            this.reset();

            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.style.background = '';
                btn.style.color = '';
                btn.style.boxShadow = '';
                btn.classList.remove('opacity-80', 'cursor-not-allowed');
                btn.disabled = false;
            }, 4000);
        })
        .catch(error => {
            btn.innerHTML = originalText;
            btn.style.background = '';
            btn.style.color = '';
            btn.style.boxShadow = '';
            btn.classList.remove('opacity-80', 'cursor-not-allowed');
            btn.disabled = false;
            showToast(error.message || 'Ocurrio un error al registrar.', 'fa-triangle-exclamation', 'linear-gradient(135deg, #ef4444 0%, #991b1b 100%)');
        });
    });
});

// ========== SMOOTH SCROLL FOR ANCHOR LINKS ==========
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});

// ========== DYNAMIC COUNTER - ANIMATE ON PAGE LOAD ==========
document.addEventListener('DOMContentLoaded', () => {
    const heroCounter = document.getElementById('heroCounter');
    if (heroCounter) {
        let startValue = 0;
        let endValue = 1247;
        let currentValue = startValue;
        
        // Animate counter from 0 to 1247
        const animationDuration = 2000; // 2 seconds
        const startTime = Date.now();
        
        function updateCounter() {
            const elapsed = Date.now() - startTime;
            const progress = Math.min(elapsed / animationDuration, 1);
            currentValue = Math.floor(startValue + (endValue - startValue) * progress);
            heroCounter.textContent = currentValue.toLocaleString();
            
            if (progress < 1) {
                requestAnimationFrame(updateCounter);
            }
        }
        
        updateCounter();
        
        // Increment counter every second with visual effect
        setInterval(() => {
            endValue += 1; // Add 1 new registration
            currentValue = parseInt(heroCounter.textContent.replace(/,/g, ''));
            const newStartTime = Date.now();
            
            // Add pulse animation effect
            heroCounter.parentElement.classList.add('animate-pulse');
            setTimeout(() => {
                heroCounter.parentElement.classList.remove('animate-pulse');
            }, 600);
            
            function updateCounterIncrement() {
                const elapsed = Date.now() - newStartTime;
                const progress = Math.min(elapsed / 300, 1); // 0.3 seconds for increment
                const newValue = Math.floor(currentValue + (endValue - currentValue) * progress);
                heroCounter.textContent = newValue.toLocaleString();
                
                if (progress < 1) {
                    requestAnimationFrame(updateCounterIncrement);
                }
            }
            
            updateCounterIncrement();
            
            // Update avatar and recent signup message every 4 seconds
            if (Math.random() < 0.25) {
                updateAvatars();
                updateRecentSignup();
            }
        }, 1000);
    }
});

// ========== DYNAMIC AVATARS WITH NAMES ==========
const firstNames = ['María', 'Carlos', 'Rosa', 'Ana', 'Jorge', 'Sofia', 'Luis', 'Diego', 'Valentina', 'Marco', 'Lucia', 'Felipe', 'Andrea', 'Roberto'];
const lastNames = ['García', 'Martínez', 'López', 'González', 'Rodríguez', 'Hernández', 'Pérez', 'Sánchez', 'Ramirez', 'Flores'];

function getRandomName() {
    const firstName = firstNames[Math.floor(Math.random() * firstNames.length)];
    const lastName = lastNames[Math.floor(Math.random() * lastNames.length)];
    return { firstName, lastName, initials: firstName[0] + lastName[0] };
}

const colors = ['bg-primary-100 text-primary-700', 'bg-blue-100 text-blue-700', 'bg-amber-100 text-amber-700', 'bg-purple-100 text-purple-700'];

function updateAvatars() {
    const avatarsContainer = document.querySelector('.flex.-space-x-2');
    if (avatarsContainer) {
        const avatars = avatarsContainer.querySelectorAll('div');
        // Shift avatars left (remove first one, add new one at end)
        const firstAvatar = avatars[0];
        firstAvatar.style.animation = 'slideLeft 0.5s ease-out forwards';
        
        setTimeout(() => {
            const newName = getRandomName();
            const newAvatar = document.createElement('div');
            const randomColor = colors[Math.floor(Math.random() * colors.length)];
            newAvatar.className = `w-8 h-8 rounded-full ${randomColor} border-2 border-white flex items-center justify-center text-xs font-bold`;
            newAvatar.textContent = newName.initials;
            
            // Remove oldest avatar after animation
            firstAvatar.remove();
            avatarsContainer.appendChild(newAvatar);
        }, 500);
    }
}
