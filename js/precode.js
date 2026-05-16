(function ($) {
    tinymce.PluginManager.add('precode', function (editor) {

        if (mce.float_toolbar) {
            editor.on('wptoolbar', function (event) {
                if (is_show_all_toolbar(event)) {
                    var all_toolbar = get_all_toolbar(event, editor.selection.getContent());
                    if (all_toolbar) {
                        event.toolbar = all_toolbar;
                    }
                }
            });
        }

        function is_show_all_toolbar(event) {
            if (event.toolbar) {
                return false;
            }

            if ($(tinyMCE.activeEditor.getContent()).length <= 4) {
                return false;
            }
            return true;
        }

        function get_all_toolbar(event, getContent) {
            var bars = [];
            var nodeName = event.element.nodeName;
            var innerText = $.trim(event.element.innerText);

            if ($.inArray(nodeName, ['P', 'H1', 'H2', 'H3', 'H4', 'H5', 'UL', 'LI', 'STRONG', 'SPAN', 'CODE']) !== -1) {

                var parentElement = event.element.parentElement;
                if (parentElement.id === 'tinymce' || ($.inArray(nodeName, ['SPAN']) !== -1 && parentElement && parentElement.parentElement.id === 'tinymce')) {
                    bars.push('zib_h2', 'zib_h3');
                }

                if (innerText) {
                    //有内容了
                    bars.push('bullist', 'numlist', 'aligncenter');
                    if (($.inArray(nodeName, ['P']) !== -1 && parentElement && parentElement.id === 'tinymce')) {
                        bars.push('zib_quote', 'zib_hide');
                    }
                    bars.push('dom_remove');
                } else {
                    bars.push('bullist', 'zib_quote', 'zib_hide');
                    if (!mce.is_admin) {
                        //后台
                        bars.push('zib_img', 'zib_video');
                    }
                    bars.push('precode');
                }

                if (getContent) {
                    //选中了部分文字
                    bars = [];
                    if ($.inArray(nodeName, ['H1', 'H2', 'H3', 'H4', 'H5']) === -1) {
                        bars = ['bold', 'link', 'wp_code', 'code'];
                    }
                    bars.push('forecolor');
                    if ($.inArray(nodeName, ['P']) !== -1) {
                        bars.push('zib_quote', 'zib_hide');
                    }
                    bars.push('remove');
                }
            }

            if (!bars[0] && $.inArray(nodeName, ['DIV', 'PRE']) !== -1 && !$(event.element).hasClass('file-download-name')) {
                bars.push('dom_remove');
            }

            return bars[0] ? editor.wp._createToolbar(bars) : false;
        }

        function toggleFormat(fmt) {
            editor.formatter.toggle(fmt)
            editor.nodeChanged()
        }

        editor.addButton('zib_h2', {
            title: 'Heading 2',
            icon: 'c-svg heading2',
            stateSelector: 'h2',
            onclick: function () {
                toggleFormat('h2')
            },
            onPostRender: function () {
                $('.mce-i-c-svg.heading2').replaceWith('<svg viewBox="0 0 1024 1024" style="width: 21px;height: 21px;fill: currentColor;"><path d="M143.616 219.648v228.864h278.016V219.648h89.856V768H421.632v-242.688H143.616V768H53.76V219.648h89.856z m660.48-10.752c52.992 0 96.768 15.36 131.328 46.08 33.792 30.72 50.688 69.888 50.688 119.04 0 47.616-18.432 90.624-53.76 129.792-16.554667 17.706667-43.093333 39.082667-78.933333 64.426667l-22.613334 15.701333-12.117333 8.192c-52.309333 34.389333-85.248 64.810667-99.413333 91.178667l-2.730667 5.589333h270.336V768h-382.464c0-56.064 17.664-104.448 54.528-145.92 8.746667-10.069333 21.76-22.186667 38.912-36.352l15.786667-12.586667c5.589333-4.352 11.52-8.874667 17.834666-13.568l19.84-14.506666 21.888-15.488 11.690667-8.106667c35.328-24.576 59.904-45.312 75.264-61.44 23.808-26.88 36.096-56.064 36.096-86.784 0-29.952-8.448-52.224-23.808-66.816-16.128-14.592-39.936-21.504-71.424-21.504-33.792 0-59.136 11.52-76.032 34.56-15.36 19.541333-24.362667 48.64-27.050667 86.058667l-0.597333 11.477333h-89.856c0.768-61.44 18.432-110.592 53.76-148.224 36.096-39.936 83.712-59.904 142.848-59.904z"></path></svg>');
            }
        });

        editor.addButton('zib_h3', {
            title: 'Heading 3',
            icon: 'c-svg heading3',
            stateSelector: 'h3',
            onclick: function () {
                toggleFormat('h3')
            },
            onPostRender: function () {
                $('.mce-i-c-svg.heading3').replaceWith('<svg viewBox="0 0 1024 1024" style="width: 21px;height: 21px;fill: currentColor;"><path d="M801.024 208.896c55.296 0 100.608 13.056 134.4 39.936 33.024 26.88 49.92 63.744 49.92 111.36 0 59.904-30.72 99.84-91.392 119.808 32.256 9.984 57.6 24.576 74.496 44.544 18.432 20.736 27.648 47.616 27.648 79.872 0 50.688-17.664 92.16-52.992 124.416-36.864 33.024-85.248 49.92-145.152 49.92-56.832 0-102.912-14.592-137.472-43.776-38.4-32.256-59.904-79.872-64.512-141.312h91.392c1.536 35.328 12.288 62.976 33.792 82.176 19.2 17.664 44.544 26.88 76.032 26.88 34.56 0 62.208-9.984 82.176-29.184 17.664-17.664 26.88-39.168 26.88-65.28 0-31.488-9.984-54.528-28.416-69.12-18.432-15.36-45.312-22.272-80.64-22.272h-38.4V449.28h38.4c32.256 0 56.832-6.912 73.728-20.736 16.128-13.824 24.576-34.56 24.576-61.44 0-26.88-7.68-46.848-22.272-60.672-16.128-13.824-39.936-20.736-71.424-20.736-32.256 0-56.832 7.68-74.496 23.808-18.432 16.128-29.184 40.704-32.256 73.728h-88.32c4.608-55.296 24.576-98.304 61.44-129.024 34.56-30.72 79.104-45.312 132.864-45.312z m-657.408 10.752v228.864h278.016V219.648h89.856V768H421.632v-242.688H143.616V768H53.76V219.648h89.856z"></path></svg>');
            }
        });

        editor.addButton('dom_remove', {
            tooltip: 'Remove',
            icon: 'dashicon dashicons-no-alt',
            onclick: function () {
                remove(editor.selection.getNode());
            }
        });

        //------------下方是高亮代码--------------
        var is_edit = false;
        var precode_toolbar;

        editor.on('wptoolbar', function (event) {
            if (is_precode(event.element)) {
                event.toolbar = precode_toolbar;
            }
        });

        editor.once('preinit', function () {
            if (editor.wp && editor.wp._createToolbar) {
                precode_toolbar = editor.wp._createToolbar([
                    'precode_edit', 'dom_remove'
                ], true);
            }
        });

        function remove(node) {
            editor.dom.remove(node);
            editor.nodeChanged();
            editor.undoManager.add();
        }

        function is_precode(e) {
            return (e.lastChild && "CODE" == e.lastChild.nodeName)
        }

        function get_val(e) {
            if (!is_precode(e)) return false;
            return {
                codeyy: e.lastChild.getAttribute("data-enlighter-language") || 'generic',
                theme: e.lastChild.getAttribute("data-enlighter-theme") || 'qj',
                codenr: e.lastChild.innerText || '',
            }
        }

        function on_submit(e) {
            var codenr = $.trim(tinymce.html.Entities.encodeAllRaw(e.data.codenr.replace(/\r\n/gmi, '\n'))),
                tm = e.data.theme == 'qj' ? '' : '&nbsp;data-enlighter-theme="' + e.data.theme + '"',
                yy = e.data.codeyy == 'generic' ? '' : '&nbsp;data-enlighter-language="' + e.data.codeyy + '"';

            if (codenr) {
                editor.insertContent('<pre contenteditable="false" class="enlighter-pre"><code class="gl"' + yy + tm + '>' + codenr + '</code></pre>' + (is_edit ? '' : '<p></p>'));
            }
        }

        function on_click() {
            var getNode = editor.selection.getNode();
            var getval = get_val(getNode);
            var vals = {
                codeyy: 'generic',
                theme: 'qj',
                codenr: '',
            }

            is_edit = !!getval;

            open(getval || vals);
        }


        function open(vals) {

            var w = Math.min(window.innerWidth);
            var h = Math.min(window.innerHeight);
            if (w > 800) {
                w = w * 0.5
            } else if (w > 640 && w < 801) {
                w = w * 0.7
            } else if (w < 641) {
                w = w - 20
            }

            var window_title = (is_edit ? '编辑' : '插入') + '高亮代码';

            editor.windowManager.open({
                title: window_title,
                width: w,
                height: h * 0.6,
                body: [{
                    type: 'listbox',
                    name: 'codeyy',
                    label: '选择语言',
                    value: vals.codeyy,
                    values: [{
                        text: 'yaml',
                        value: 'yaml'
                    }, {
                        text: 'xml/html',
                        value: 'xml'
                    }, {
                        text: 'visualbasic',
                        value: 'visualbasic'
                    }, {
                        text: 'vhdl',
                        value: 'vhdl'
                    }, {
                        text: 'typescript',
                        value: 'typescript'
                    }, {
                        text: 'swift',
                        value: 'swift'
                    }, {
                        text: 'squirrel',
                        value: 'squirrel'
                    }, {
                        text: 'sql',
                        value: 'sql'
                    }, {
                        text: 'shell',
                        value: 'shell'
                    }, {
                        text: 'scss/sass',
                        value: 'scss'
                    }, {
                        text: 'rust',
                        value: 'rust'
                    }, {
                        text: 'ruby',
                        value: 'ruby'
                    }, {
                        text: 'raw',
                        value: 'raw'
                    }, {
                        text: 'python',
                        value: 'python'
                    }, {
                        text: 'prolog',
                        value: 'prolog'
                    }, {
                        text: 'powershell',
                        value: 'powershell'
                    }, {
                        text: 'php',
                        value: 'php'
                    }, {
                        text: 'nsis',
                        value: 'nsis'
                    }, {
                        text: 'matlab',
                        value: 'matlab'
                    }, {
                        text: 'markdown',
                        value: 'markdown'
                    }, {
                        text: 'lua',
                        value: 'lua'
                    }, {
                        text: 'less',
                        value: 'less'
                    }, {
                        text: 'kotlin',
                        value: 'kotlin'
                    }, {
                        text: 'json',
                        value: 'json'
                    }, {
                        text: 'javascript',
                        value: 'javascript'
                    }, {
                        text: 'java',
                        value: 'java'
                    }, {
                        text: 'ini/conf',
                        value: 'ini'
                    }, {
                        text: 'groovy',
                        value: 'groovy'
                    }, {
                        text: 'go/golang',
                        value: 'go'
                    }, {
                        text: 'docker',
                        value: 'dockerfile'
                    }, {
                        text: 'diff',
                        value: 'diff'
                    }, {
                        text: 'cordpro',
                        value: 'cordpro'
                    }, {
                        text: 'cython',
                        value: 'cython'
                    }, {
                        text: 'css',
                        value: 'css'
                    }, {
                        text: 'csharp',
                        value: 'csharp'
                    }, {
                        text: 'Cpp/C++/C',
                        value: 'cpp'
                    }, {
                        text: 'avrassembly',
                        value: 'avrassembly'
                    }, {
                        text: 'assembly',
                        value: 'assembly'
                    }, {
                        text: '通用高亮',
                        value: 'generic'
                    }],
                }, {
                    type: "listbox",
                    name: "theme",
                    label: "主题",
                    value: vals.theme,
                    values: [{
                        text: 'enlighter',
                        value: 'enlighter'
                    }, {
                        text: 'classic',
                        value: 'classic'
                    }, {
                        text: 'beyond',
                        value: 'beyond'
                    }, {
                        text: 'mowtwo',
                        value: 'mowtwo'
                    }, {
                        text: 'eclipse',
                        value: 'eclipse'
                    }, {
                        text: 'droide',
                        value: 'droide'
                    }, {
                        text: 'minimal',
                        value: 'minimal'
                    }, {
                        text: 'atomic',
                        value: 'atomic'
                    }, {
                        text: 'dracula',
                        value: 'dracula'
                    }, {
                        text: 'bootstrap4',
                        value: 'bootstrap4'
                    }, {
                        text: 'rowhammer',
                        value: 'rowhammer'
                    }, {
                        text: 'godzilla',
                        value: 'godzilla'
                    }, {
                        text: '跟随全局设置',
                        value: 'qj'
                    }],
                }, {
                    type: 'textbox',
                    name: 'codenr',
                    label: '代码：',
                    value: vals.codenr,
                    multiline: true,
                    minHeight: h * 0.6 - 115
                }],
                onsubmit: on_submit
            });
        }

        $.each(['precode', 'precode_edit'], function (i, k) {
            editor.addButton(k, {
                title: (k === 'precode' ? '' : '编辑') + '高亮代码', //标题自拟
                icon: 'c-svg zib-precode',
                onPostRender: function () {
                    $('.mce-i-c-svg.zib-precode').replaceWith('<svg viewBox="0 0 1024 1024" style="width: 20px;height: 20px;fill: currentColor;"><path d="M902.4 454.4l-144-144a40.704 40.704 0 0 0-57.6 57.6L844.8 512l-144 144a40.704 40.704 0 0 0 57.6 57.6L902.4 569.6a81.472 81.472 0 0 0 0-115.2zM265.6 310.4L121.6 454.4a81.472 81.472 0 0 0 0 115.2l144 144a40.704 40.704 0 0 0 57.6-57.6L179.2 512l144-144a40.704 40.704 0 0 0-57.6-57.6z m109.568 544.064L570.24 147.904a40.704 40.704 0 0 1 78.528 21.632l-195.072 706.56a40.704 40.704 0 0 1-78.528-21.696z"></path></svg>');
                },
                stateSelector: 'pre',
                onclick: on_click
            });
        })

    });
})(jQuery);