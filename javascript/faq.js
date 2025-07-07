document.addEventListener('DOMContentLoaded', function () {
    const faqBtn = document.getElementById('faq-button');
    const faqModal = document.getElementById('faq-modal');
    const closeBtn = document.getElementById('faq-close');
    const faqQuestions = document.querySelectorAll('.faq-question');

    faqBtn.addEventListener('click', () => {
        faqModal.classList.toggle('hidden');
    });

    closeBtn.addEventListener('click', () => {
        faqModal.classList.add('hidden');
    });

    faqQuestions.forEach(q => {
        q.addEventListener('click', () => {
            const answer = q.nextElementSibling;
            answer.style.display = (answer.style.display === 'block') ? 'none' : 'block';
        });
    });
});