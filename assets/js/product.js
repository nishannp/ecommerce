// Product detail page specific functionality
$(document).ready(function() {
    // Image zoom effect on hover
    const mainImage = document.getElementById('mainImage');
    
    if (mainImage) {
        mainImage.addEventListener('mousemove', function(e) {
            const x = e.clientX - e.target.offsetLeft;
            const y = e.clientY - e.target.offsetTop;
            
            const imgWidth = e.target.offsetWidth;
            const imgHeight = e.target.offsetHeight;
            
            const xPercent = (x / imgWidth) * 100;
            const yPercent = (y / imgHeight) * 100;
            
            e.target.style.transformOrigin = `${xPercent}% ${yPercent}%`;
        });
        
        mainImage.addEventListener('mouseenter', function() {
            mainImage.style.transform = 'scale(1.5)';
        });
        
        mainImage.addEventListener('mouseleave', function() {
            mainImage.style.transform = 'scale(1)';
        });
    }
    
    // Validate quantity input (only accept numbers)
    $('#quantity').on('input', function() {
        const value = $(this).val();
        
        if (value < 1) {
            $(this).val(1);
        }
    });
    
    // Add input event to prevent non-numeric values
    $('#quantity').on('keypress', function(e) {
        if (isNaN(String.fromCharCode(e.which)) && e.which !== 8) {
            e.preventDefault();
        }
    });
});