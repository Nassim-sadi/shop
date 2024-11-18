// import imageCompression from "browser-image-compression";

// /**
//  * Asynchronously compresses an image file.
//  *
//  * @param {File} file Image file to compress
//  * @param {number} [maxSizeMB=2] Max size in MB to compress to
//  * @returns {Promise<File | null>} Compressed image file or null if compression fails
//  */
// export async function useImageCompression(file, maxSizeMB = 2) {
//     const options = {
//         maxSizeMB, // Max size in MB (default: 2MB)
//         maxWidthOrHeight: 1000, // Max width or height (default: 1000px)
//         useWebWorker: true, // Use web worker for better performance
//     };

//     try {
//         // Compress the image and return the compressed file
//         let imageFile = await imageCompression(file, options);
//         const preview = URL.createObjectURL(imageFile);
//         const compressedImage = new File([imageFile], file.name.split(".")[0], {
//             type: imageFile.type,
//         });
//         return { compressedImage, preview };
//     } catch (error) {
//         console.error("Image compression failed:", error);
//         return error; // Return null if compression fails
//     }
// }

import imageCompression from "browser-image-compression";

/**
 * Compresses an image file and provides a preview URL.
 *
 * @param {File} file Image file to compress
 * @param {number} [maxSizeMB=2] Max size in MB for compression
 * @returns {Promise<{ compressedImage: File, preview: string } | null>} Compressed image file and preview URL
 */
export async function useImageCompression(file, maxSizeMB = 2) {
    const options = {
        maxSizeMB, // Maximum size in MB
        maxWidthOrHeight: 1000, // Resize the image if needed
        useWebWorker: true, // Improve performance with a web worker
    };

    try {
        let imageFile = await imageCompression(file, options);
        const preview = URL.createObjectURL(imageFile);
        const compressedImage = new File([imageFile], file.name.split(".")[0], {
            type: imageFile.type,
        });
        return { compressedImage, preview };
    } catch (error) {
        console.error("Image compression failed:", error);
        return null;
    }
}
