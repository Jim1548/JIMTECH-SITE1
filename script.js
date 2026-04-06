const navLinks = document.querySelectorAll('nav ul li a');
const sections = document.querySelectorAll('section');
const header = document.querySelector('header');
const scrollTop = document.getElementById('scrollTop');
const revealElements = document.querySelectorAll('.reveal');
const counters = document.querySelectorAll('.counter');
const projectModal = document.getElementById('projectModal');
const openProjectFormButton = document.getElementById('openProjectForm');
const modalCloseButton = document.querySelector('.modal-close');

const showToast = (message) => {
    let toast = document.querySelector('.toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.className = 'toast';
        document.body.appendChild(toast);
    }
    toast.textContent = message;
    toast.classList.add('show');
    window.setTimeout(() => toast.classList.remove('show'), 3200);
};

const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            revealObserver.unobserve(entry.target);
        }
    });
}, {
    threshold: 0.18,
});

revealElements.forEach((element) => revealObserver.observe(element));

const updateActiveNav = () => {
    const scrollPosition = window.scrollY + 130;
    sections.forEach((section) => {
        const top = section.offsetTop;
        const bottom = top + section.offsetHeight;
        const sectionId = section.getAttribute('id');

        if (scrollPosition >= top && scrollPosition <= bottom) {
            navLinks.forEach((link) => {
                link.classList.toggle('active', link.getAttribute('href') === `#${sectionId}`);
            });
        }
    });
};

const countUp = (entry) => {
    const target = Number(entry.target.dataset.target);
    const duration = 1700;
    let start = 0;
    const step = (timestamp) => {
        if (!entry.startTime) entry.startTime = timestamp;
        const progress = Math.min((timestamp - entry.startTime) / duration, 1);
        entry.target.textContent = Math.floor(progress * (target - start) + start);
        if (progress < 1) {
            requestAnimationFrame(step);
        }
    };
    requestAnimationFrame(step);
};

const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
        if (entry.isIntersecting) {
            countUp(entry);
            counterObserver.unobserve(entry.target);
        }
    });
}, { threshold: 0.5 });

counters.forEach((counter) => counterObserver.observe(counter));

window.addEventListener('scroll', () => {
    const scroll = window.scrollY;
    header.classList.toggle('scrolled', scroll > 20);
    scrollTop.classList.toggle('visible', scroll > 560);
    updateActiveNav();
});

scrollTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

document.querySelectorAll('form').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const currentForm = event.currentTarget;
        const formData = new FormData(currentForm);
        if (!formData.get('source')) {
            formData.set('source', currentForm.id === 'projectForm' ? 'project-modal' : 'contact-section');
        }

        try {
            const response = await fetch(currentForm.action || 'submit_quote.php', {
                method: 'POST',
                body: formData,
            });
            const result = await response.json();

            if (!response.ok || result.status !== 'success') {
                showToast(result.message || 'Erreur lors de l’envoi de la demande.');
                return;
            }

            currentForm.reset();
            showToast(result.message || 'Merci ! Votre demande a bien été enregistrée.');
            if (projectModal?.classList.contains('active')) {
                hideProjectModal();
            }
        } catch (error) {
            console.error(error);
            showToast('Impossible de contacter le serveur. Veuillez réessayer plus tard.');
        }
    });
});

const showProjectModal = () => {
    if (!projectModal) return;
    projectModal.classList.add('active');
    projectModal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    const firstInput = projectModal.querySelector('input');
    if (firstInput) firstInput.focus();
};

const hideProjectModal = () => {
    if (!projectModal) return;
    projectModal.classList.remove('active');
    projectModal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
};

if (openProjectFormButton) {
    openProjectFormButton.addEventListener('click', showProjectModal);
}

if (modalCloseButton) {
    modalCloseButton.addEventListener('click', hideProjectModal);
}

if (projectModal) {
    projectModal.addEventListener('click', (event) => {
        if (event.target === projectModal || event.target.classList.contains('project-modal-backdrop')) {
            hideProjectModal();
        }
    });
}

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && projectModal?.classList.contains('active')) {
        hideProjectModal();
    }
});

navLinks.forEach((link) => {
    link.addEventListener('click', () => {
        navLinks.forEach((nav) => nav.classList.remove('active'));
        link.classList.add('active');
    });
});

updateActiveNav();

// Typing effect for hero title
const typeWriter = (element, text, speed = 100) => {
    let i = 0;
    element.textContent = '';
    const timer = setInterval(() => {
        if (i < text.length) {
            element.textContent += text.charAt(i);
            i++;
        } else {
            clearInterval(timer);
            setTimeout(() => typeWriter(element, text, speed), 2000); // Wait 2 seconds then restart
        }
    }, speed);
};

const heroTitle = document.querySelector('.hero h1');
if (heroTitle) {
    const text = heroTitle.getAttribute('data-text') || heroTitle.textContent;
    typeWriter(heroTitle, text);
}
