<x-app-layout>
    <div class="p-6">
        <h2 class="text-xl font-bold mb-4">🌳 Work Category Tree</h2>
        <div id="jstree"></div>
    </div>

    <!-- JS & CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jstree/dist/themes/default/style.min.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jstree@3.3.12/dist/jstree.min.js"></script>
    
    <script>
    $(document).ready(function () {
        $('#jstree').jstree({
            'core': {
                'data': {
                    'url': '/eng/workscategory/tree-json',
                    'dataType': 'json'
                },
                'check_callback': true
            },
            'plugins': ['contextmenu', 'dnd', 'wholerow'],
            'contextmenu': {
                'items': function (node) {
                    var tree = $('#jstree').jstree(true);
                    return {
                        create: {
                            label: "Create",
                            action: function () {
                                var newNode = tree.create_node(node, { text: "New node" });
                                tree.edit(newNode);
                            }
                        },
                        rename: {
                            label: "Rename",
                            action: function () {
                                tree.edit(node);
                            }
                        },
                        remove: {
                            label: "Delete",
                            action: function () {
                                if (confirm("Yakin ingin menghapus node ini?")) {
                                    $.ajax({
                                        url: `/eng/workscategory/delete/${node.id}`,
                                        method: 'POST',
                                        data: {
                                            _token: '{{ csrf_token() }}'
                                        },
                                        success: function (res) {
                                            if (res.success) {
                                                tree.delete_node(node);
                                                alert('Node berhasil dihapus');
                                            }
                                        },
                                        error: function () {
                                            alert('Gagal menghapus node');
                                        }
                                    });
                                }
                            }
                        },
                        // copy: {
                        //     label: "Copy",
                        //     action: function () {
                        //         tree.copy(node);
                        //     }
                        // },
                        // cut: {
                        //     label: "Cut",
                        //     action: function () {
                        //         tree.cut(node);
                        //     }
                        // },
                        // paste: {
                        //     label: "Paste",
                        //     action: function () {
                        //         tree.paste(node);
                        //     }
                        // }
                    };
                }
            }
        })

        // Tandai bahwa node baru belum disimpan
        .on('create_node.jstree', function (e, data) {
            data.node.data = { isNew: true };
        })

        // Simpan node saat rename
        .on('rename_node.jstree', function (e, data) {
            const isNew = data.node.data && data.node.data.isNew;
            const url = isNew ? '/eng/workscategory/store' : '/eng/workscategory/update';

            const payload = {
                text: data.text,
                parent: data.node.parent,
                _token: '{{ csrf_token() }}'
            };

            if (!isNew) {
                payload.id = data.node.id;
            }

            $.post(url, payload, function (res) {
                if (res.success) {
                    if (isNew) {
                        $('#jstree').jstree(true).set_id(data.node, res.id);
                        alert('Node berhasil dibuat');
                    } else {
                        alert('Update berhasil');
                    }
                }
            });
        })

        // Simpan saat node dipindah
        .on('move_node.jstree', function (e, data) {
            $.post('/eng/workscategory/update', {
                id: data.node.id,
                text: data.node.text,
                parent: data.parent,
                _token: '{{ csrf_token() }}'
            }, function (res) {
                if (res.success) {
                    alert('Parent berhasil diubah');
                }
            });
        });

        
    });
</script>

</x-app-layout>
