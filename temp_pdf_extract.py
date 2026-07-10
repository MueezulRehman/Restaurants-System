from pathlib import Path
try:
    import PyPDF2
except ImportError:
    raise SystemExit('PyPDF2 not installed')

p = Path('resources/views/tastehut-system-design-v2.4.pdf')
print('EXISTS', p.exists())
if not p.exists():
    raise SystemExit('PDF missing')
with p.open('rb') as f:
    reader = PyPDF2.PdfReader(f)
    for i, page in enumerate(reader.pages, start=1):
        print(f'--PAGE {i}--')
        text = page.extract_text()
        print(text if text is not None else '<no text>')
