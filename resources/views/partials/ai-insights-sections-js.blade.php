{{-- Shared AI-insights section helpers (supervisor + school head).
     Mirrors App\Models\PreObservationPlanning::insightsSections() and the
     partials.ai-insights-display look, so per-section Modify editors can
     collect, rebuild (canonical ## markdown) and re-render organized
     previews entirely client-side. Include once per page inside its scripts. --}}
<script>
window.AiInsightsSections = (function () {
    var ORDER = ['lesson_focus', 'key_things_to_watch', 'conference_talking_points', 'potential_challenges'];
    var LABELS = {
        lesson_focus: 'Lesson Focus',
        key_things_to_watch: 'Key Things to Watch',
        conference_talking_points: 'Pre-Conference Talking Points',
        potential_challenges: 'Potential Challenges'
    };
    var HEADINGS = {
        'lesson focus': 'lesson_focus',
        'lesson plan goals & focus': 'lesson_focus',
        'lesson plan goals and focus': 'lesson_focus',
        'key things to watch': 'key_things_to_watch',
        'key focus areas': 'key_things_to_watch',
        'teaching strategies to observe': 'key_things_to_watch',
        'teaching strategies': 'key_things_to_watch',
        'materials & resources': 'key_things_to_watch',
        'materials and resources': 'key_things_to_watch',
        'assessment methods': 'key_things_to_watch',
        'conference talking points': 'conference_talking_points',
        'pre conference talking points': 'conference_talking_points',
        'pre conference discussion points': 'conference_talking_points',
        'potential challenges': 'potential_challenges',
        'lesson plan completeness': 'potential_challenges',
        'lesson plan note': 'potential_challenges'
    };
    var STYLES = {
        lesson_focus: { accent: 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400', bar: 'border-indigo-200 dark:border-indigo-800', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>' },
        key_things_to_watch: { accent: 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400', bar: 'border-blue-200 dark:border-blue-800', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>' },
        conference_talking_points: { accent: 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400', bar: 'border-amber-200 dark:border-amber-800', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>' },
        potential_challenges: { accent: 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400', bar: 'border-red-200 dark:border-red-800', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>' }
    };
    var FALLBACK_STYLE = { accent: 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400', bar: 'border-gray-200 dark:border-gray-700', icon: '' };
    var FIELD_CLASS = 'w-full px-3 py-2 rounded-lg border border-amber-300 dark:border-amber-700 dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm';
    var FIELD_META = {
        lesson_focus: { label: 'Lesson Focus', hint: 'Short paragraph — what the lesson aims to achieve.', rows: 3 },
        key_things_to_watch: { label: 'Key Things to Watch', hint: 'One concrete item per line.', rows: 4 },
        conference_talking_points: { label: 'Pre-Conference Talking Points', hint: 'One question per line.', rows: 4 },
        potential_challenges: { label: 'Potential Challenges', hint: 'One risk per line.', rows: 3 }
    };
    var CHEVRON = '<svg class="w-3.5 h-3.5 mt-1 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>';

    function trimMarkers(s) {
        return s.replace(/^[*`_ \t\n\r]+/, '').replace(/[*`_ \t\n\r]+$/, '');
    }

    function normalizeHeading(s) {
        s = (s || '').replace(/^#{1,6}\s+/, '');
        s = s.replace(/^(\d+[.)]\s*|[-*\u2022]\s*)+/, '');
        s = trimMarkers(s).replace(/:+$/, '');
        s = trimMarkers(s).replace(/[_—–-]/g, ' ').replace(/\s+/g, ' ');
        return s.toLowerCase();
    }

    function matchHeading(line) {
        if (line.indexOf(':') !== -1) {
            var idx = line.indexOf(':');
            var normHead = normalizeHeading(line.slice(0, idx));
            if (HEADINGS[normHead]) {
                var rest = line.slice(idx + 1).replace(/^[*`_ \t\n\r]+/, '').replace(/[*`_ \t\n\r]+$/, '');
                rest = rest.replace(/^(\d+[.)]\s*|[-*\u2022]\s*)+/, '').trim();
                return { key: HEADINGS[normHead], inline: rest };
            }
        }
        var norm = normalizeHeading(line);
        if (HEADINGS[norm]) {
            return { key: HEADINGS[norm], inline: '' };
        }
        return null;
    }

    function cleanItem(line) {
        return line.replace(/^(\d+[.)]\s*|[-*\u2022]\s*)+/, '').trim()
            .replace(/\*\*(.+?)\*\*/g, '$1').replace(/`(.+?)`/g, '$1')
            .split('**').join('').split('__').join('').split('`').join('');
    }

    // Split markdown into { shape: 'sections'|'raw'|'empty', sections: {...} }.
    function split(markdown) {
        var text = (markdown || '').trim();
        if (!text) {
            return { shape: 'empty', sections: {} };
        }
        var buckets = { lesson_focus: [], key_things_to_watch: [], conference_talking_points: [], potential_challenges: [] };
        var current = null;
        var lines = text.split(/\r?\n/);
        for (var i = 0; i < lines.length; i++) {
            var line = lines[i].replace(/^\s+|\s+$/g, '');
            if (!line || line === '---' || line === '* * *') {
                continue;
            }
            if (/^(\*\*|__)?pre[\s\-_]*observation insights for\b/i.test(line)) {
                continue;
            }
            var hit = matchHeading(line);
            if (hit) {
                current = hit.key;
                if (hit.inline) {
                    buckets[current].push(hit.inline);
                }
                continue;
            }
            if (current === null) {
                continue;
            }
            buckets[current].push(line);
        }
        var sections = {};
        var parsed = false;
        ORDER.forEach(function (key) {
            var items = buckets[key].map(cleanItem).filter(function (x) { return x !== ''; });
            if (!items.length) {
                return;
            }
            sections[key] = (key === 'lesson_focus') ? items.join(' ') : items;
            parsed = true;
        });
        if (!parsed) {
            return { shape: 'raw', sections: { raw: text } };
        }
        return { shape: 'sections', sections: sections };
    }

    // Canonical ## markdown (round-trips through the server parser).
    function toMarkdown(sections) {
        if (sections.raw !== undefined) {
            return sections.raw || '';
        }
        var out = [];
        ORDER.forEach(function (key) {
            var val = sections[key];
            if (val === undefined || val === null) {
                return;
            }
            var items = Array.isArray(val) ? val : [val];
            items = items.map(function (x) { return (x || '').trim(); }).filter(function (x) { return x !== ''; });
            if (!items.length) {
                return;
            }
            out.push('## ' + LABELS[key]);
            if (key === 'lesson_focus') {
                out.push(items.join(' '));
            } else {
                items.forEach(function (item) { out.push('- ' + item); });
            }
            out.push('');
        });
        return out.join('\n').replace(/\s+$/, '');
    }

    function escapeHtml(s) {
        return (s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function prettyLabel(key) {
        return key.replace(/_/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); });
    }

    // Organized section cards HTML (same look as partials.ai-insights-display).
    function renderPreview(sections) {
        if (sections.raw !== undefined) {
            return '<p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap">' + escapeHtml(sections.raw) + '</p>';
        }
        var html = '<div class="space-y-3 min-w-0">';
        ORDER.forEach(function (key) {
            var val = sections[key];
            if (val === undefined) {
                return;
            }
            var items = Array.isArray(val) ? val : [val];
            items = items.map(function (x) { return (x || '').trim(); }).filter(function (x) { return x !== ''; });
            if (!items.length) {
                return;
            }
            var style = STYLES[key] || FALLBACK_STYLE;
            var label = LABELS[key] || prettyLabel(key);
            html += '<div class="rounded-xl border ' + style.bar + ' bg-white/60 dark:bg-gray-800/50 overflow-hidden">'
                + '<div class="flex items-center gap-2 px-3 py-2 border-b ' + style.bar + '">'
                + (style.icon ? '<span class="shrink-0 ' + style.accent + ' rounded-lg p-1.5">' + style.icon + '</span>' : '')
                + '<span class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">' + escapeHtml(label) + '</span>'
                + '</div><div class="px-4 py-3">';
            if (key === 'lesson_focus') {
                html += '<p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">' + escapeHtml(items.join(' ')) + '</p>';
            } else {
                html += '<ul class="space-y-2.5">';
                items.forEach(function (item) {
                    html += '<li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300 leading-relaxed">' + CHEVRON + '<span class="min-w-0">' + escapeHtml(item) + '</span></li>';
                });
                html += '</ul>';
            }
            html += '</div></div>';
        });
        html += '</div>';
        return html;
    }

    // Per-section editor fields HTML (same look as partials.ai-insights-editors).
    function renderEditors(sections) {
        sections = sections || {};
        if (sections.raw !== undefined) {
            return '<div><label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">AI Insights</label>'
                + '<textarea data-insight-field="raw" rows="8" class="' + FIELD_CLASS + '">' + escapeHtml(sections.raw) + '</textarea></div>';
        }
        var html = '';
        ORDER.forEach(function (key) {
            var meta = FIELD_META[key];
            var val = sections[key];
            var text = '';
            if (val !== undefined) {
                text = Array.isArray(val) ? val.join('\n') : val;
            }
            html += '<div><label class="block text-xs font-semibold text-gray-600 dark:text-gray-300">' + meta.label + '</label>'
                + '<p class="text-[11px] text-gray-400 dark:text-gray-500 mb-1">' + meta.hint + '</p>'
                + '<textarea data-insight-field="' + key + '" rows="' + meta.rows + '" class="' + FIELD_CLASS + '">' + escapeHtml(text) + '</textarea></div>';
        });
        return html;
    }

    // Read editor fields back into { key: string|array }.
    function collectFrom(container) {
        var sections = {};
        if (!container) {
            return sections;
        }
        var fields = container.querySelectorAll('[data-insight-field]');
        for (var i = 0; i < fields.length; i++) {
            var key = fields[i].getAttribute('data-insight-field');
            var val = fields[i].value || '';
            if (key === 'raw' || key === 'lesson_focus') {
                sections[key] = val.trim();
            } else {
                sections[key] = val.split('\n').map(function (x) { return x.trim(); }).filter(function (x) { return x !== ''; });
            }
        }
        return sections;
    }

    return {
        ORDER: ORDER,
        LABELS: LABELS,
        split: split,
        toMarkdown: toMarkdown,
        renderPreview: renderPreview,
        renderEditors: renderEditors,
        collectFrom: collectFrom,
        escapeHtml: escapeHtml
    };
})();
</script>
