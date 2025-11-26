// GMS Icon Generator - Creates PNG versions of the SVG icon
class GMSIconGenerator {
    constructor() {
        this.canvas = document.createElement('canvas');
        this.ctx = this.canvas.getContext('2d');
    }
    
    // Generate icon at specified size
    generateIcon(size = 32) {
        this.canvas.width = size;
        this.canvas.height = size;
        this.ctx.clearRect(0, 0, size, size);
        
        const scale = size / 128; // Base size is 128x128
        
        // Background circle with gradient
        const gradient = this.ctx.createRadialGradient(
            size * 0.5, size * 0.5, 0,
            size * 0.5, size * 0.5, size * 0.47
        );
        gradient.addColorStop(0, '#7C3AED');
        gradient.addColorStop(0.5, '#6366F1');
        gradient.addColorStop(1, '#4F46E5');
        
        this.ctx.fillStyle = gradient;
        this.ctx.beginPath();
        this.ctx.arc(size * 0.5, size * 0.5, size * 0.47, 0, Math.PI * 2);
        this.ctx.fill();
        
        // Add shadow
        this.ctx.shadowColor = 'rgba(0, 0, 0, 0.3)';
        this.ctx.shadowBlur = size * 0.03;
        this.ctx.shadowOffsetX = 0;
        this.ctx.shadowOffsetY = size * 0.02;
        
        // Mountains
        this.ctx.fillStyle = '#1F2937';
        this.ctx.beginPath();
        this.ctx.moveTo(size * 0.19, size * 0.75);
        this.ctx.lineTo(size * 0.38, size * 0.5);
        this.ctx.lineTo(size * 0.56, size * 0.63);
        this.ctx.lineTo(size * 0.75, size * 0.44);
        this.ctx.lineTo(size * 0.81, size * 0.75);
        this.ctx.closePath();
        this.ctx.fill();
        
        // Second mountain layer
        this.ctx.fillStyle = '#374151';
        this.ctx.beginPath();
        this.ctx.moveTo(size * 0.25, size * 0.75);
        this.ctx.lineTo(size * 0.44, size * 0.56);
        this.ctx.lineTo(size * 0.63, size * 0.69);
        this.ctx.lineTo(size * 0.81, size * 0.5);
        this.ctx.lineTo(size * 0.88, size * 0.75);
        this.ctx.closePath();
        this.ctx.fill();
        
        // Reset shadow
        this.ctx.shadowColor = 'transparent';
        this.ctx.shadowBlur = 0;
        this.ctx.shadowOffsetX = 0;
        this.ctx.shadowOffsetY = 0;
        
        // Sun
        const sunGradient = this.ctx.createRadialGradient(
            size * 0.75, size * 0.25, 0,
            size * 0.75, size * 0.25, size * 0.125
        );
        sunGradient.addColorStop(0, '#F59E0B');
        sunGradient.addColorStop(0.5, '#F97316');
        sunGradient.addColorStop(1, '#EF4444');
        
        this.ctx.fillStyle = sunGradient;
        this.ctx.beginPath();
        this.ctx.arc(size * 0.75, size * 0.25, size * 0.125, 0, Math.PI * 2);
        this.ctx.fill();
        
        // Sun rays
        this.ctx.fillStyle = sunGradient;
        this.ctx.save();
        this.ctx.translate(size * 0.75, size * 0.25);
        
        for (let i = 0; i < 8; i++) {
            this.ctx.rotate(Math.PI / 4);
            this.ctx.fillRect(-size * 0.01, -size * 0.06, size * 0.02, size * 0.06);
        }
        this.ctx.restore();
        
        // Compass in center
        this.ctx.strokeStyle = '#FCD34D';
        this.ctx.lineWidth = size * 0.023;
        this.ctx.lineCap = 'round';
        
        // Compass circle
        this.ctx.beginPath();
        this.ctx.arc(size * 0.5, size * 0.5, size * 0.094, 0, Math.PI * 2);
        this.ctx.stroke();
        
        // Compass cross
        this.ctx.beginPath();
        this.ctx.moveTo(size * 0.5, size * 0.406);
        this.ctx.lineTo(size * 0.5, size * 0.594);
        this.ctx.moveTo(size * 0.406, size * 0.5);
        this.ctx.lineTo(size * 0.594, size * 0.5);
        this.ctx.stroke();
        
        // Center dot
        this.ctx.fillStyle = '#FCD34D';
        this.ctx.beginPath();
        this.ctx.arc(size * 0.5, size * 0.5, size * 0.031, 0, Math.PI * 2);
        this.ctx.fill();
        
        // Trees
        this.ctx.fillStyle = '#059669';
        this.drawTree(size * 0.31, size * 0.69, size * 0.06);
        this.drawTree(size * 0.63, size * 0.63, size * 0.06);
        this.drawTree(size * 0.47, size * 0.72, size * 0.06);
        
        // GMS Text (only for larger sizes)
        if (size >= 64) {
            this.ctx.fillStyle = 'white';
            this.ctx.font = `bold ${size * 0.125}px Arial`;
            this.ctx.textAlign = 'center';
            this.ctx.fillText('GMS', size * 0.5, size * 0.94);
        }
        
        return this.canvas;
    }
    
    // Draw a simple triangular tree
    drawTree(x, y, size) {
        this.ctx.fillStyle = '#059669';
        this.ctx.beginPath();
        this.ctx.moveTo(x, y);
        this.ctx.lineTo(x - size, y + size);
        this.ctx.lineTo(x + size, y + size);
        this.ctx.closePath();
        this.ctx.fill();
    }
    
    // Download the icon as PNG
    downloadIcon(size = 32, filename = 'gms-icon') {
        const canvas = this.generateIcon(size);
        const link = document.createElement('a');
        link.download = `${filename}-${size}x${size}.png`;
        link.href = canvas.toDataURL('image/png');
        link.click();
    }
    
    // Get the icon as data URL
    getIconDataURL(size = 32) {
        const canvas = this.generateIcon(size);
        return canvas.toDataURL('image/png');
    }
}

// Auto-generate icons when page loads
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window !== 'undefined') {
        window.GMSIconGenerator = GMSIconGenerator;
        
        // Create icon previews
        const previewContainer = document.getElementById('icon-preview');
        if (previewContainer) {
            const generator = new GMSIconGenerator();
            
            // Generate different sizes
            [32, 64, 128].forEach(size => {
                const canvas = generator.generateIcon(size);
                const img = document.createElement('img');
                img.src = canvas.toDataURL('image/png');
                img.alt = `GMS Icon ${size}x${size}`;
                img.style.margin = '10px';
                img.style.border = '1px solid #ddd';
                img.style.borderRadius = '4px';
                previewContainer.appendChild(img);
            });
        }
    }
});

// Export for Node.js if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = GMSIconGenerator;
}

