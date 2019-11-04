# Vendor

And last but not least, most projects will have a vendors/ folder containing all the CSS files from external libraries and frameworks – Normalize, Bootstrap, jQueryUI, FancyCarouselSliderjQueryPowered, and so on. Putting those aside in the same folder is a good way to say “Hey, this is not from me, not my code, not my responsibility”.

# Use YARN Package manager instead

**Note:** Instead of downloading JS libraries to this folder, there is a better solution. Use **yarn** to install any of your main packages and then import them into assets/js/app.js instead of adding them to this folder.
Use this folder as a fallback if yarn can't find the JS library you want to install then you can add it here.

Reference: https://sass-guidelin.es/#vendors-folder
