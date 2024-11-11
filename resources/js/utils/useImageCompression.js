import imageCompression from "browser-image-compression";

/**
 * Asynchronously compresses an image file.
 *
 * @param {File} file Image file to compress
 * @param {number} [maxSizeMB=2] Max size in MB to compress to
 * @returns {Promise<File | null>} Compressed image file or null if compression fails
 */
export async function useImageCompression(file, maxSizeMB = 2) {
    const options = {
        maxSizeMB, // Max size in MB (default: 2MB)
        maxWidthOrHeight: 1000, // Max width or height (default: 1000px)
        useWebWorker: true, // Use web worker for better performance
    };

    try {
        // Compress the image and return the compressed file
        let imageFile = await imageCompression(file, options);
        const preview = URL.createObjectURL(imageFile);
        const compressedImage = new File(
            [imageFile.value],
            file.name.split(".")[0],
            {
                type: imageFile.type,
            },
        );
        return { compressedImage, preview };
    } catch (error) {
        console.error("Image compression failed:", error);
        return null; // Return null if compression fails
    }
}
