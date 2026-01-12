/**
 * Video Utilities
 * Handles YouTube URL parsing and conversion to embed format
 */

/**
 * Converts various YouTube URL formats to the standard embed URL
 * @param {string} url - The YouTube video URL
 * @returns {string|null} - The embeddable URL or null if invalid
 */
export const getYouTubeEmbedUrl = (url) => {
    if (!url) return null;

    // If it's already an embed URL, return it
    if (url.includes('youtube.com/embed/')) return url;

    try {
        const urlObj = new URL(url);
        let videoId = '';

        if (urlObj.hostname === 'youtu.be') {
            // Shortened URL: youtu.be/ID
            videoId = urlObj.pathname.slice(1);
        } else if (urlObj.hostname.includes('youtube.com')) {
            // Standard URL: youtube.com/watch?v=ID
            videoId = urlObj.searchParams.get('v');

            // Handle case where it's /shorts/ID
            if (!videoId && urlObj.pathname.startsWith('/shorts/')) {
                videoId = urlObj.pathname.split('/')[2];
            }
        }

        if (videoId) {
            // Remove any extra params like t= or si=
            const cleanId = videoId.split('?')[0].split('&')[0];
            return `https://www.youtube.com/embed/${cleanId}`;
        }
    } catch (e) {
        console.error("Invalid YouTube URL:", url);
    }

    return null;
};
