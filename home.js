document.addEventListener('DOMContentLoaded', () => {
  const fadeInElements = document.querySelectorAll('.fade-in');
  const header = document.querySelector('header');

  const observer = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        observer.unobserve(entry.target);
      }
    });
  }, {
    threshold: 0.1
  });

  fadeInElements.forEach(element => {
    observer.observe(element);
  });

  window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  });

  const testimonialsContainer = document.querySelector('.testimonial-cards');
  let testimonials = JSON.parse(localStorage.getItem('testimonials')) || [];
  
  testimonials.forEach(testimonial => {
    const card = document.createElement('div');
    card.classList.add('card');
    card.innerHTML = `<p>"${testimonial.message}"</p><h3>- ${testimonial.name}</h3>`;
    testimonialsContainer.appendChild(card);
  });
});
