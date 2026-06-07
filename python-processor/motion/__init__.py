"""Motion detection helpers for the video processor."""

from motion.clip_writer import ClipWriter
from motion.detector import MotionDetectionResult, MotionDetector
from motion.stream_processor import SavedClip, StreamProcessor

__all__ = [
    "ClipWriter",
    "MotionDetectionResult",
    "MotionDetector",
    "SavedClip",
    "StreamProcessor",
]
