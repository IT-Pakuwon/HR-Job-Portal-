"""Extract nonempty worksheet cells without evaluating Excel formulas."""
import json
from decimal import Decimal
import pathlib
import posixpath
import xml.etree.ElementTree as ET
import zipfile

NS = {'m': 'http://schemas.openxmlformats.org/spreadsheetml/2006/main'}

def extract(path):
    with zipfile.ZipFile(path) as archive:
        strings = []
        if 'xl/sharedStrings.xml' in archive.namelist():
            strings = [''.join(t.text or '' for t in item.findall('.//m:t', NS))
                       for item in ET.fromstring(archive.read('xl/sharedStrings.xml')).findall('m:si', NS)]
        relations = {r.attrib['Id']: r.attrib['Target'] for r in
                      ET.fromstring(archive.read('xl/_rels/workbook.xml.rels'))}
        sheets = []
        for sheet in ET.fromstring(archive.read('xl/workbook.xml')).findall('m:sheets/m:sheet', NS):
            rid = sheet.attrib['{http://schemas.openxmlformats.org/officeDocument/2006/relationships}id']
            target = relations[rid]
            target = target.lstrip('/') if target.startswith('/') else posixpath.normpath('xl/' + target)
            rows = []
            for row in ET.fromstring(archive.read(target)).findall('.//m:sheetData/m:row', NS):
                cells = {}
                for cell in row.findall('m:c', NS):
                    value = cell.find('m:v', NS)
                    text = value.text if value is not None else None
                    if cell.attrib.get('t') == 's' and text is not None:
                        text = strings[int(text)]
                    elif cell.attrib.get('t') == 'inlineStr':
                        text = ''.join(t.text or '' for t in cell.findall('.//m:t', NS))
                    if text is not None and text.strip():
                        cells[cell.attrib['r']] = text.strip()
                if cells:
                    rows.append(cells)
            sheets.append({'name': sheet.attrib['name'], 'rows': rows})
        return {'file': path.name, 'sheets': sheets}

def templates(books):
    codes = ['FIRE-ALARM', 'AHU-FCU-WCPU-AC', 'CHILLER', 'COOLING-TOWER',
             'ELEVATOR', 'ESCALATOR-TRAVELATOR', 'GONDOLA', 'SLIDING-DOOR', 'STP']
    assert len(books) == len(codes) == 9
    result = []
    for book, code in zip(books, codes):
        assert len(book['sheets']) == 1
        rows = [{''.join(filter(str.isalpha, k)): v for k, v in row.items()}
                for row in book['sheets'][0]['rows']]
        template = {'id': 'VPE-' + code, 'description': next(r['C'] for r in rows if r.get('A') == 'Jenis Pekerjaan'),
                    'source': book['file'], 'aspects': [], 'scores': [], 'result_categories': []}
        current = None
        categories = False
        for row in rows:
            if row.get('A', '').isdigit() and 'B' in row and 'C' in row:
                current = {'id': f"{template['id']}-A{int(row['A']):02}", 'description': row['B'],
                           'weight': str((Decimal(row['C']) * 100).quantize(Decimal('0.01'))), 'details': []}
                template['aspects'].append(current)
            elif current is not None and 'B' in row and 'C' in row and 'D' in row:
                current['details'].append({'id': f"{current['id']}-S{len(current['details']) + 1:02}",
                                           'description': row['B'], 'criteria': row['D'], 'note': row.get('G'),
                                           'weight': str((Decimal(row['C']) * 100).quantize(Decimal('0.01')))})
            if row.get('A') == 'TOTAL NILAI KPI':
                current = None
            if row.get('I', '').isdigit() and row.get('J', '').isdigit():
                template['scores'].append({'score': int(row['I']), 'value': int(row['J']),
                                           'label': row['A'], 'description': row['D']})
            if row.get('A') == 'Kategori Hasil Akhir Vendor':
                categories = True
            elif categories and 'A' in row and 'D' in row:
                template['result_categories'].append({'range': row['A'], 'label': row['D']})
        assert len(template['aspects']) == 5, template['id']
        assert sum(Decimal(a['weight']) for a in template['aspects']) == 100, template['id']
        for aspect in template['aspects']:
            assert sum(Decimal(d['weight']) for d in aspect['details']) == Decimal(aspect['weight']), aspect['id']
            assert all(d['criteria'] and d['note'] for d in aspect['details']), aspect['id']
        assert sorted((s['score'], s['value']) for s in template['scores']) == [(1, 40), (2, 60), (3, 80), (4, 100), (5, 120)]
        assert len(template['result_categories']) == 5
        result.append(template)
    return result

if __name__ == '__main__':
    files = sorted(p for p in pathlib.Path(r'C:\Users\AW\Downloads').glob('*Form Evaluasi Kinerja Vendor*.xlsx') if not p.name.startswith('~$'))
    data = [extract(path) for path in files]
    output = pathlib.Path('database/data/vendor_evaluation_source.json')
    output.parent.mkdir(parents=True, exist_ok=True)
    output.write_text(json.dumps(data, ensure_ascii=False, indent=2), encoding='utf-8')
    normalized = templates(data)
    output.with_name('vendor_evaluation_templates.json').write_text(json.dumps(normalized, ensure_ascii=False, indent=2), encoding='utf-8')
    for template in normalized:
        print(template['id'], template['description'], len(template['aspects']), 'aspects,',
              sum(len(a['details']) for a in template['aspects']), 'details, total weight 100%')
