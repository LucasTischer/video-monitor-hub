import sys
from pathlib import Path

import numpy as np

sys.path.insert(0, str(Path(__file__).resolve().parents[1]))

from motion.detector import MotionDetector


def blank_frame():
    return np.zeros((120, 160, 3), dtype=np.uint8)


def frame_with_rectangle(size=60):
    frame = blank_frame()
    frame[20 : 20 + size, 20 : 20 + size] = 255

    return frame


def test_first_frame_initializes_background_without_motion():
    detector = MotionDetector(resize_width=160)

    result = detector.process(blank_frame())

    assert result.motion_detected is False
    assert result.contour_count == 0
    assert result.largest_contour_area == 0


def test_large_frame_change_is_detected_as_motion():
    detector = MotionDetector(min_area=500, resize_width=160)

    detector.process(blank_frame())
    result = detector.process(frame_with_rectangle())

    assert result.motion_detected is True
    assert result.contour_count >= 1
    assert result.largest_contour_area >= 500
    assert result.threshold_frame is not None
    assert result.delta_frame is not None


def test_small_frame_change_is_ignored():
    detector = MotionDetector(min_area=500, resize_width=160)

    detector.process(blank_frame())
    result = detector.process(frame_with_rectangle(size=5))

    assert result.motion_detected is False


def test_reset_reinitializes_background():
    detector = MotionDetector(min_area=500, resize_width=160)

    detector.process(blank_frame())
    detector.reset()
    result = detector.process(frame_with_rectangle())

    assert result.motion_detected is False
    assert result.contour_count == 0
