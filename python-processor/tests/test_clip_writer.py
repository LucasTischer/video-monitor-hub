import sys
from pathlib import Path

import numpy as np

sys.path.insert(0, str(Path(__file__).resolve().parents[1]))

from motion.clip_writer import ClipWriter


class FakeVideoWriter:
    instances = []

    def __init__(self, output_path, fourcc, fps, frame_size, is_color):
        self.output_path = output_path
        self.fourcc = fourcc
        self.fps = fps
        self.frame_size = frame_size
        self.is_color = is_color
        self.frames = []
        self.released = False
        FakeVideoWriter.instances.append(self)

    def write(self, frame):
        self.frames.append(frame)

    def release(self):
        self.released = True


def make_frame(value):
    return np.full((20, 30, 3), value, dtype=np.uint8)


def test_clip_writer_writes_buffered_and_live_frames():
    FakeVideoWriter.instances = []
    writer = ClipWriter(buffer_size=3, writer_factory=FakeVideoWriter)

    writer.update(make_frame(1))
    writer.update(make_frame(2))
    writer.update(make_frame(3))
    writer.start("clip.avi", fourcc=1234, fps=30)
    writer.update(make_frame(4))
    writer.finish()

    fake_writer = FakeVideoWriter.instances[0]

    assert fake_writer.output_path == "clip.avi"
    assert fake_writer.fourcc == 1234
    assert fake_writer.fps == 30
    assert fake_writer.frame_size == (30, 20)
    assert fake_writer.released is True
    assert len(fake_writer.frames) == 4


def test_clip_writer_keeps_only_the_configured_buffer_size():
    FakeVideoWriter.instances = []
    writer = ClipWriter(buffer_size=2, writer_factory=FakeVideoWriter)

    writer.update(make_frame(1))
    writer.update(make_frame(2))
    writer.update(make_frame(3))
    writer.start("clip.avi", fourcc=1234, fps=30)
    writer.finish()

    fake_writer = FakeVideoWriter.instances[0]

    assert len(fake_writer.frames) == 2


def test_clip_writer_requires_a_buffered_frame_before_starting():
    writer = ClipWriter(writer_factory=FakeVideoWriter)

    try:
        writer.start("clip.avi", fourcc=1234, fps=30)
    except RuntimeError as exception:
        assert "without buffered frames" in str(exception)
    else:
        raise AssertionError("Expected RuntimeError")


def test_finish_is_safe_when_not_recording():
    writer = ClipWriter(writer_factory=FakeVideoWriter)

    writer.finish()

    assert writer.recording is False
