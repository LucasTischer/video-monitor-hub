from collections import deque
from queue import Empty, Queue
from threading import Event, Thread
from typing import Callable

import cv2


class ClipWriter:
    """Write buffered frames plus live frames into a motion-triggered clip."""

    def __init__(
        self,
        buffer_size: int = 64,
        queue_timeout: float = 0.1,
        writer_factory: Callable | None = None,
    ) -> None:
        self.buffer_size = buffer_size
        self.queue_timeout = queue_timeout
        self.writer_factory = writer_factory or cv2.VideoWriter

        self.frames = deque(maxlen=buffer_size)
        self.queue: Queue | None = None
        self.writer = None
        self.thread: Thread | None = None
        self.stop_event = Event()
        self.recording = False

    def update(self, frame) -> None:
        self.frames.append(frame)

        if self.recording and self.queue is not None:
            self.queue.put(frame)

    def start(self, output_path: str, fourcc: int, fps: int | float) -> None:
        if self.recording:
            return

        if not self.frames:
            raise RuntimeError("Cannot start clip writer without buffered frames.")

        frame = self.frames[-1]
        height, width = frame.shape[:2]

        self.queue = Queue()
        self.stop_event.clear()
        self.writer = self.writer_factory(output_path, fourcc, fps, (width, height), True)

        for buffered_frame in self.frames:
            self.queue.put(buffered_frame)

        self.recording = True
        self.thread = Thread(target=self._write_loop, daemon=True)
        self.thread.start()

    def finish(self) -> None:
        if not self.recording:
            return

        self.recording = False
        self.stop_event.set()

        if self.thread is not None:
            self.thread.join()

        self._flush()

        if self.writer is not None:
            self.writer.release()

        self.queue = None
        self.writer = None
        self.thread = None

    def _write_loop(self) -> None:
        while not self.stop_event.is_set():
            self._write_next_frame()

    def _flush(self) -> None:
        while self.queue is not None and not self.queue.empty():
            self._write_next_frame(block=False)

    def _write_next_frame(self, block: bool = True) -> None:
        if self.queue is None or self.writer is None:
            return

        try:
            frame = self.queue.get(block=block, timeout=self.queue_timeout)
        except Empty:
            return

        self.writer.write(frame)
