# GMS System Icons

This directory contains professional icons for the Guide Management System (GMS).

## Available Icons

### SVG Icons (Recommended)
- **`gms-icon.svg`** - 32x32 icon for browser tabs and general use
- **`gms-icon-large.svg`** - 128x128 icon for high-resolution displays

### PNG Icons (Generated)
Use the icon generator to create PNG versions at any size:
- 16x16 (favicon)
- 32x32 (browser tab)
- 48x48 (desktop shortcut)
- 64x64 (high-DPI displays)
- 128x128 (app icons)
- 256x256 (retina displays)

## How to Use

### 1. Browser Tab Icon
The SVG icon is automatically linked in `header.php` and will appear in browser tabs.

### 2. Generate PNG Icons
1. Open `generate-favicon.html` in your browser
2. Click "Download All PNG Icons" to get all sizes
3. Use the generated PNG files as needed

### 3. Create favicon.ico
To create a favicon.ico file:

**Option 1: Online Converter**
1. Go to [favicon.io](https://favicon.io/) or [realfavicongenerator.net](https://realfavicongenerator.net/)
2. Upload `gms-icon-large.svg`
3. Download the generated favicon.ico

**Option 2: ImageMagick (Command Line)**
```bash
convert gms-icon-large.svg -resize 16x16 favicon.ico
```

**Option 3: GIMP/Photoshop**
1. Open `gms-icon-large.svg`
2. Export as PNG at 16x16
3. Convert to ICO format

## Icon Design

The GMS icon features:
- **Mountain Theme**: Represents outdoor activities and guides
- **Compass Element**: Symbolizes guidance and direction
- **Professional Colors**: Purple gradient with gold accents
- **Modern Design**: Clean, scalable vector graphics

## File Structure
```
assets/
├── images/
│   ├── gms-icon.svg          # 32x32 SVG icon
│   ├── gms-icon-large.svg    # 128x128 SVG icon
│   └── README.md             # This file
└── js/
    └── icon-generator.js     # PNG icon generator
```

## Browser Support

- **SVG Icons**: Modern browsers (Chrome, Firefox, Safari, Edge)
- **PNG Icons**: All browsers
- **ICO Files**: All browsers (traditional favicon format)

## Customization

To modify the icon:
1. Edit the SVG files in a vector graphics editor (Inkscape, Adobe Illustrator)
2. Maintain the 32x32 and 128x128 viewBox dimensions
3. Keep the gradient definitions for consistent colors
4. Test at different sizes to ensure readability

## Troubleshooting

**Icon not showing in browser tab:**
1. Clear browser cache
2. Check file paths in `header.php`
3. Verify SVG files are accessible
4. Check browser console for errors

**Icon looks blurry:**
1. Use SVG format for best quality
2. Generate PNG at 2x the display size for high-DPI displays
3. Ensure proper viewBox settings in SVG

