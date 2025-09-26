import express from 'express';
import multer from 'multer';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import { getFeatureFlag, getFeatureFlagValue, updateFeatureFlag } from './feature-flags.js';
// Importar TensorFlow.js
import * as tf from '@tensorflow/tfjs-node';

const router = express.Router();

// Multer configuration
const storage = multer.memoryStorage();
const upload = multer({ 
    storage: storage,
    limits: { fileSize: 10 * 1024 * 1024 } // 10MB limit
});

// Global variables for model
let model = null;
let modelLoading = false;
let modelLoadError = null;

// Class labels
const classLabels = ["Neumonía", "No Neumonía"];

// Get current directory path
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Function to list directories and files
function listDirectoryContents(dir) {
    try {
        if (!getFeatureFlag('enable-model-diagnostics')) return;
        
        console.log(`\nListing contents of: ${dir}`);
        if (!fs.existsSync(dir)) {
            console.log(`  Directory does not exist: ${dir}`);
            return;
        }
        
        const items = fs.readdirSync(dir);
        items.forEach(item => {
            const itemPath = path.join(dir, item);
            const stats = fs.statSync(itemPath);
            if (stats.isDirectory()) {
                console.log(`  📁 ${item}/`);
            } else {
                console.log(`  📄 ${item} (${(stats.size / 1024).toFixed(2)} KB)`);
            }
        });
    } catch (error) {
        console.error(`Error listing directory ${dir}:`, error);
    }
}

// Function to load the model with feature flags
async function loadModel() {
    if (modelLoading) return;
    
    try {
        modelLoading = true;
        modelLoadError = null;
        
        if (getFeatureFlag('enable-model-diagnostics')) {
            console.log("\n=== MODEL LOADING DIAGNOSTICS ===");
            console.log(`Current directory: ${__dirname}`);
            console.log(`Project root directory: ${path.join(__dirname, '..')}`);
            
            // List contents of root directory
            listDirectoryContents(path.join(__dirname, '..'));
        }
        
        // Get model paths based on feature flags
        const tfjsModelPaths = [];
        
        if (getFeatureFlag('model-path-local')) {
            tfjsModelPaths.push(path.join(getFeatureFlagValue('model-path-local'), 'model.json'));
        }
        
        if (getFeatureFlag('model-path-relative')) {
            tfjsModelPaths.push(path.join(__dirname, getFeatureFlagValue('model-path-relative'), 'model.json'));
        }
        
        // Add default paths
        tfjsModelPaths.push(path.join(__dirname, '../modelo_web/model.json'));
        tfjsModelPaths.push(path.join(__dirname, 'modelo_web/model.json'));
        
        // Try to load the model from any of the paths
        let modelLoaded = false;
        
        // Try to load using TFJS format
        if (getFeatureFlag('use-tfjs-model')) {
            for (const modelPath of tfjsModelPaths) {
                if (fs.existsSync(modelPath)) {
                    if (getFeatureFlag('enable-model-diagnostics')) {
                        console.log(`\nFound model.json: ${modelPath}`);
                        listDirectoryContents(path.dirname(modelPath));
                    }
                    
                    try {
                        // Load the TensorFlow.js model
                        const modelUrl = `file://${modelPath}`;
                        model = await tf.loadLayersModel(modelUrl);
                        
                        if (getFeatureFlag('enable-model-diagnostics')) {
                            console.log(`✅ Successfully loaded TFJS model from ${modelPath}`);
                            console.log("Model summary:", model.summary());
                        }
                        
                        modelLoaded = true;
                        break;
                    } catch (loadErr) {
                        if (getFeatureFlag('enable-model-diagnostics')) {
                            console.error(`Error loading TFJS model: ${loadErr.message}`);
                        }
                    }
                } else if (getFeatureFlag('enable-model-diagnostics')) {
                    console.log(`❌ model.json not found at ${modelPath}`);
                }
            }
        }
        
        // If real model loading failed and simulation is enabled, create a simulation model
        if (!modelLoaded && getFeatureFlag('use-simulation-model')) {
            if (getFeatureFlag('enable-model-diagnostics')) {
                console.log("\n### CREATING SIMULATION MODEL FOR DIAGNOSTICS ###");
                console.log("This model will return random results for testing");
            }
            
            // Simulation model object
            model = {
                predict: (tensor) => {
                    if (getFeatureFlag('enable-model-diagnostics')) {
                        console.log("Performing simulated prediction");
                    }
                    return {
                        dataSync: () => [Math.random()], // Random value between 0 and 1
                        dispose: () => {
                            if (getFeatureFlag('enable-model-diagnostics')) {
                                console.log("Freeing simulation model resources");
                            }
                        }
                    };
                },
                dispose: () => {
                    if (getFeatureFlag('enable-model-diagnostics')) {
                        console.log("Freeing simulation model");
                    }
                }
            };
            
            if (getFeatureFlag('enable-model-diagnostics')) {
                console.log("✅ Simulation model created successfully for diagnostics");
            }
            modelLoaded = true;
        }
        
        if (!modelLoaded) {
            throw new Error("Could not load model from any available path");
        }
        
        if (getFeatureFlag('enable-model-diagnostics')) {
            console.log("=== END OF DIAGNOSTICS ===\n");
        }
    } catch (error) {
        modelLoadError = error.message;
        if (getFeatureFlag('enable-model-diagnostics')) {
            console.error(`Error during diagnostics: ${error.message}`);
        }
    } finally {
        modelLoading = false;
    }
}

// Load the model when this file is imported
loadModel();

// Middleware to check if model is loaded
const checkModelLoaded = (req, res, next) => {
    if (!model) {
        if (modelLoading) {
            return res.status(503).json({ 
                error: 'Model is loading, please try again in a moment' 
            });
        }
        
        // Auto-reload if enabled
        if (getFeatureFlag('enable-auto-reload')) {
            loadModel();
            return res.status(503).json({ 
                error: 'Model is reloading, please try again in a moment' 
            });
        }
        
        return res.status(500).json({ 
            error: 'Model is not loaded', 
            details: modelLoadError 
        });
    }
    next();
};

// Helper function to preprocess the image for the model
async function preprocessImage(imageBuffer) {
    // Convert buffer to tensor
    const image = tf.node.decodeImage(imageBuffer);
    
    // Resize to 224x224 (MobileNetV2 default input size)
    const resized = tf.image.resizeBilinear(image, [224, 224]);
    
    // Normalize pixel values to [0,1]
    const normalized = resized.div(255.0);
    
    // Add batch dimension
    const batched = normalized.expandDims(0);
    
    // Clean up intermediate tensors
    image.dispose();
    resized.dispose();
    normalized.dispose();
    
    return batched;
}

// Route for prediction with model
router.post('/', upload.single('file'), checkModelLoaded, async (req, res) => {
    try {
        if (!req.file) {
            return res.status(400).json({ error: 'No file provided' });
        }

        // Verify file type
        const validMimeTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!validMimeTypes.includes(req.file.mimetype)) {
            return res.status(400).json({ 
                error: 'Invalid file type. Only JPEG/JPG or PNG images are allowed' 
            });
        }

        if (getFeatureFlag('enable-model-diagnostics')) {
            console.log("📝 Image received correctly:", req.file.originalname);
        }
        
        // Process image data using TensorFlow.js
        const imageTensor = await preprocessImage(req.file.buffer);
        
        // Make prediction
        const predictions = model.predict(imageTensor);
        const predictionArray = predictions.dataSync();
        
        // Get confidence scores
        const predictionValue = predictionArray[0];
        const pneumoniaConfidence = Math.round((1 - predictionValue) * 100 * 100) / 100;
        const normalConfidence = Math.round(predictionValue * 100 * 100) / 100;
        
        // Cleanup tensors to prevent memory leaks
        imageTensor.dispose();
        predictions.dispose();
        
        // Create response object
        let result;
        if (pneumoniaConfidence > normalConfidence) {
            result = {
                prediction: "Neumonía",
                confidence: pneumoniaConfidence,
                confidence_scores: {
                    "Neumonía": pneumoniaConfidence, 
                    "No Neumonía": normalConfidence
                },
                _diagnostic: getFeatureFlag('enable-model-diagnostics') ? "Real model prediction" : undefined
            };
        } else {
            result = {
                prediction: "No Neumonía",
                confidence: normalConfidence,
                confidence_scores: {
                    "Neumonía": pneumoniaConfidence,
                    "No Neumonía": normalConfidence
                },
                _diagnostic: getFeatureFlag('enable-model-diagnostics') ? "Real model prediction" : undefined
            };
        }

        if (getFeatureFlag('enable-model-diagnostics')) {
            console.log("✅ Sending prediction response:", result);
        }
        return res.status(200).json(result);
    } catch (error) {
        console.error(`Error processing image: ${error.message}`);
        return res.status(500).json({ 
            error: `Error processing image: ${error.message}`,
            stack: process.env.NODE_ENV === 'development' ? error.stack : undefined
        });
    }
});

// Route to check model status
router.get('/status', (req, res) => {
    res.json({ 
        modelLoaded: !!model,
        modelLoading: modelLoading,
        modelError: modelLoadError,
        classLabels: classLabels,
        featureFlags: getFeatureFlag('enable-model-diagnostics') ? getAllFeatureFlags() : undefined,
        message: getFeatureFlag('use-simulation-model') ? "This is a simulation model for diagnostics" : undefined
    });
});

// Route to force model reload
router.post('/reload', async (req, res) => {
    try {
        // Release current model if it exists
        if (model) {
            if (model.dispose) {
                model.dispose();
            }
            model = null;
        }
        
        // Start loading process
        await loadModel();
        
        res.json({
            success: true,
            modelLoaded: !!model,
            error: modelLoadError,
            message: getFeatureFlag('use-simulation-model') ? "This is a simulation model for diagnostics" : undefined
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

// Helper function to get all feature flags
function getAllFeatureFlags() {
    // Implementation would need to be provided based on your feature-flags.js module
    // This is a placeholder
    return {};
}

// New route to manage feature flags
router.get('/features', (req, res) => {
    res.json({
        features: getAllFeatureFlags()
    });
});

// Route to update a feature flag
router.post('/features/:flagName', (req, res) => {
    const { flagName } = req.params;
    const { enabled, value } = req.body;
    
    if (enabled === undefined) {
        return res.status(400).json({
            error: 'Enabled status is required'
        });
    }
    
    const updated = updateFeatureFlag(flagName, enabled, value);
    
    if (!updated) {
        return res.status(404).json({
            error: `Feature flag ${flagName} not found`
        });
    }
    
    // If the flag affects model loading, reload the model
    const modelFlags = [
        'use-h5-model', 
        'use-tfjs-model', 
        'use-simulation-model',
        'model-path-local',
        'model-path-relative'
    ];
    
    if (modelFlags.includes(flagName)) {
        if (model && model.dispose) {
            model.dispose();
            model = null;
        }
        loadModel();
    }
    
    res.json({
        success: true,
        flag: {
            name: flagName,
            enabled,
            value
        }
    });
});

export default router;