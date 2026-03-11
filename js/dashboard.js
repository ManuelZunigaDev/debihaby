/* Dashboard Interactivity - DebiHaby */

document.addEventListener('DOMContentLoaded', () => {
    console.log('DebiHaby Dashboard loaded');

    // Add click events to available path nodes
    const availableNodes = document.querySelectorAll('.path-node.available');
    availableNodes.forEach((node, index) => {
        node.addEventListener('click', () => {
            // For demo, we just use index + 1 or 1
            window.location.href = `lesson.php?id=1`;
        });
    });

    // === Sidebar is handled via PHP includes now ===

    // Simple micro-animation for stat pills on hover
    const statPills = document.querySelectorAll('.stat-pill');
    statPills.forEach(pill => {
        pill.addEventListener('mouseenter', () => {
            pill.style.transform = 'translateY(-2px)';
            pill.style.transition = 'transform 0.2s ease';
        });
        pill.addEventListener('mouseleave', () => {
            pill.style.transform = 'translateY(0)';
        });
    });

    // Mock progress update animation
    const progressBar = document.querySelector('.progress-bar');
    if (progressBar) {
        setTimeout(() => {
            progressBar.style.width = '75%';
            progressBar.style.transition = 'width 1.5s cubic-bezier(0.1, 0.7, 1.0, 0.1)';
        }, 500);
    }
});
