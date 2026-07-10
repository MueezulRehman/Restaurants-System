from pathlib import Path
import PyPDF2

pdf_path = Path('resources/views/tastehut-system-design-v2.4.pdf')
out_path = Path('temp_design_extract.txt')

print('exists', pdf_path.exists(), 'size', pdf_path.stat().st_size if pdf_path.exists() else None)
reader = PyPDF2.PdfReader(str(pdf_path))
print('pages', len(reader.pages))
text = '\n'.join(page.extract_text() or '' for page in reader.pages)
out_path.write_text(text, encoding='utf-8')
print('wrote', out_path)
print(text[:20000])
