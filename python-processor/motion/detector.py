from dataclasses import dataclass

import cv2


@dataclass(frozen=True)
class MotionDetectionResult:
    motion_detected: bool
    contour_count: int
    largest_contour_area: float
    processed_frame: object
    threshold_frame: object | None = None
    delta_frame: object | None = None


class MotionDetector:
    """Detect meaningful frame changes using a running background average."""

    def __init__(
        self,
        min_area: int = 1000,
        resize_width: int = 720,
        blur_size: tuple[int, int] = (21, 21),
        threshold_value: int = 25,
        dilate_iterations: int = 2,
        average_weight: float = 0.3,
    ) -> None:
        self.min_area = min_area
        self.resize_width = resize_width
        self.blur_size = blur_size
        self.threshold_value = threshold_value
        self.dilate_iterations = dilate_iterations
        self.average_weight = average_weight
        self.average_frame = None

    def process(self, frame) -> MotionDetectionResult:
        processed_frame = self._resize(frame)
        gray_frame = cv2.cvtColor(processed_frame, cv2.COLOR_BGR2GRAY)
        gray_frame = cv2.GaussianBlur(gray_frame, self.blur_size, 0)

        if self.average_frame is None:
            self.average_frame = gray_frame.copy().astype("float")

            return MotionDetectionResult(
                motion_detected=False,
                contour_count=0,
                largest_contour_area=0,
                processed_frame=processed_frame,
            )

        delta_frame = cv2.absdiff(gray_frame, cv2.convertScaleAbs(self.average_frame))
        threshold_frame = cv2.threshold(
            delta_frame,
            self.threshold_value,
            255,
            cv2.THRESH_BINARY,
        )[1]
        threshold_frame = cv2.dilate(threshold_frame, None, iterations=self.dilate_iterations)

        contours = cv2.findContours(
            threshold_frame.copy(),
            cv2.RETR_EXTERNAL,
            cv2.CHAIN_APPROX_SIMPLE,
        )
        contours = contours[0] if len(contours) == 2 else contours[1]

        contour_areas = [cv2.contourArea(contour) for contour in contours]
        largest_contour_area = max(contour_areas, default=0)
        motion_detected = any(area >= self.min_area for area in contour_areas)

        cv2.accumulateWeighted(gray_frame, self.average_frame, self.average_weight)

        return MotionDetectionResult(
            motion_detected=motion_detected,
            contour_count=len(contours),
            largest_contour_area=largest_contour_area,
            processed_frame=processed_frame,
            threshold_frame=threshold_frame,
            delta_frame=delta_frame,
        )

    def reset(self) -> None:
        self.average_frame = None

    def _resize(self, frame):
        height, width = frame.shape[:2]

        if width == self.resize_width:
            return frame.copy()

        ratio = self.resize_width / width
        dimensions = (self.resize_width, int(height * ratio))

        return cv2.resize(frame, dimensions, interpolation=cv2.INTER_AREA)
