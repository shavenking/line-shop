<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>新竹梅屁股</title>
    <script charset="utf-8" src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ mix('js/app.js') }}"></script>
</head>
<body style="margin: 0">
<div style="height: 100vh; width: 100vw">
    <iframe id="gform" src="" frameborder="0" style="width: 100%; height: 100%;"></iframe>
</div>
<script>
    liff.init({
        liffId: {{ \Illuminate\Support\Js::from(config('line-shop.liff_id')) }}, // Use own liffId
        withLoginOnExternalBrowser: true,
    }).then(() => {
        const lineProfile = liff.getDecodedIDToken();
        const iframe = document.getElementById('gform');
        let onloadCount = 0;
        iframe.onload = () => {
            onloadCount += 1
            if (onloadCount > 1) {
                onloadCount = 0;

                Swal.fire({
                    title: '下單成功',
                    text: '訂單金額計算中，請勿關閉視窗',
                })
            }
        }
        iframe.src = `
                    {{ config('line-shop.g_form_url') }}
        &{{ config('line-shop.g_form_purchaser_entry') }}=${lineProfile.name}
                `;

        Echo.channel(`google-forms.${sha1(lineProfile.name).toString()}`)
            .listen('GoogleFormSubmitted', (e) => {
                const data = e.data;
                liff.sendMessages([
                    {
                        "type": "flex",
                        "altText": "this is a flex message",
                        "contents": {
                            "type": "bubble",
                            "body": {
                                "type": "box",
                                "layout": "vertical",
                                "contents": [
                                    {
                                        "type": "text",
                                        "text": "下單成功！",
                                        "weight": "bold",
                                        "color": "#1DB446",
                                        "size": "sm"
                                    },
                                    {
                                        "type": "text",
                                        "text": '新竹梅屁股',
                                        "weight": "bold",
                                        "size": "xxl",
                                        "margin": "md"
                                    },
                                    {
                                        "type": "separator",
                                        "margin": "xxl"
                                    },
                                    {
                                        "type": "box",
                                        "layout": "vertical",
                                        "margin": "xxl",
                                        "spacing": "sm",
                                        "contents": [
                                            ...data.order_items.map(orderItem => {
                                                return {
                                                    "type": "box",
                                                    "layout": "horizontal",
                                                    "contents": [
                                                        {
                                                            "type": "text",
                                                            "text": orderItem['name'],
                                                            "size": "sm",
                                                            "color": "#555555",
                                                            "flex": 0
                                                        },
                                                        {
                                                            "type": "text",
                                                            "text": '$' + `${orderItem['price']} x ${orderItem['quantity']}`,
                                                            "size": "sm",
                                                            "color": "#111111",
                                                            "align": "end"
                                                        }
                                                    ]
                                                }
                                            }),
                                            data['giveaway_quantity'] > 0 ? {
                                                "type": "box",
                                                "layout": "horizontal",
                                                "contents": [
                                                    {
                                                        "type": "text",
                                                        "text": `*贈70元商品x ${data['giveaway_quantity']} 份`,
                                                        "wrap": true,
                                                        "size": "sm",
                                                        "color": "#111111",
                                                        "align": "end"
                                                    }
                                                ]
                                            } : {
                                                "type": "box",
                                                "layout": "horizontal",
                                                "contents": []
                                            },
                                            {
                                                "type": "separator",
                                                "margin": "xxl"
                                            },
                                            {
                                                "type": "box",
                                                "margin": "md",
                                                "layout": "horizontal",
                                                "contents": [
                                                    {
                                                        "type": "text",
                                                        "text": "小計",
                                                        "size": "sm",
                                                        "color": "#555555"
                                                    },
                                                    {
                                                        "type": "text",
                                                        "text": '$' + data['order_item_total'],
                                                        "size": "sm",
                                                        "color": "#111111",
                                                        "align": "end"
                                                    }
                                                ]
                                            },
                                            {
                                                "type": "box",
                                                "layout": "horizontal",
                                                "contents": [
                                                    {
                                                        "type": "text",
                                                        "text": "運費",
                                                        "size": "sm",
                                                        "color": "#555555"
                                                    },
                                                    {
                                                        "type": "text",
                                                        "text": (data['is_offshore_islands'] ? '（離島）' : '') + '$' + data['shipping_fee'],
                                                        "size": "sm",
                                                        "color": "#111111",
                                                        "align": "end"
                                                    }
                                                ]
                                            },
                                            {
                                                "type": "box",
                                                "layout": "horizontal",
                                                "contents": [
                                                    {
                                                        "type": "text",
                                                        "text": "總價",
                                                        "size": "sm",
                                                        "color": "#555555"
                                                    },
                                                    {
                                                        "type": "text",
                                                        "text": '$' + data['total_amount'],
                                                        "size": "sm",
                                                        "color": "#111111",
                                                        "align": "end"
                                                    }
                                                ]
                                            }
                                        ]
                                    },
                                    // {
                                    //     "type": "separator",
                                    //     "margin": "xxl"
                                    // },
                                    // {
                                    //     "type": "box",
                                    //     "margin": "md",
                                    //     "layout": "horizontal",
                                    //     "contents": [
                                    //         {
                                    //             "type": "text",
                                    //             "text": "戶名：黃香梅",
                                    //             "size": "sm",
                                    //             "color": "#555555"
                                    //         }
                                    //     ]
                                    // },
                                    // {
                                    //     "type": "box",
                                    //     "layout": "horizontal",
                                    //     "contents": [
                                    //         {
                                    //             "type": "text",
                                    //             "text": "銀行：台新銀行 敦南分行 812",
                                    //             "size": "sm",
                                    //             "color": "#555555"
                                    //         },
                                    //     ]
                                    // },
                                    // {
                                    //     "type": "box",
                                    //     "layout": "horizontal",
                                    //     "contents": [
                                    //         {
                                    //             "type": "text",
                                    //             "text": "帳號：28881003432265",
                                    //             "size": "sm",
                                    //             "color": "#555555"
                                    //         },
                                    //     ]
                                    // },
                                    // {
                                    //     "type": "box",
                                    //     "layout": "horizontal",
                                    //     "contents": [
                                    //         {
                                    //             "type": "text",
                                    //             "text": "匯款期限：" + data['last_money_transfer_date'] + ' 晚上 12 點前，並留言給小編您的帳號後五碼，以方便我們對帳哦！',
                                    //             "wrap": true,
                                    //             "size": "sm",
                                    //             "color": "#555555"
                                    //         },
                                    //     ]
                                    // },
                                    // {
                                    //     "type": "box",
                                    //     "layout": "horizontal",
                                    //     "contents": [
                                    //         {
                                    //             "type": "text",
                                    //             "text": "到貨日期：" + data['arrival_date'],
                                    //             "wrap": true,
                                    //             "size": "sm",
                                    //             "color": "#555555"
                                    //         },
                                    //     ]
                                    // },
                                    // {
                                    //     "type": "box",
                                    //     "layout": "horizontal",
                                    //     "contents": [
                                    //         {
                                    //             "type": "text",
                                    //             "text": "到貨時段：" + data['arrival_time'],
                                    //             "wrap": true,
                                    //             "size": "sm",
                                    //             "color": "#555555"
                                    //         },
                                    //     ]
                                    // },
                                    // {
                                    //     "type": "separator",
                                    //     "margin": "xxl"
                                    // },
                                    // {
                                    //     "type": "box",
                                    //     "layout": "horizontal",
                                    //     "margin": "md",
                                    //     "contents": [
                                    //         {
                                    //             "type": "text",
                                    //             "text": '如有問題，請在下方留言，我們會儘速爲您服務～',
                                    //             "wrap": true,
                                    //             "size": "xs",
                                    //             "color": "#aaaaaa",
                                    //             "flex": 0
                                    //         },
                                    //     ]
                                    // }
                                ]
                            },
                            "styles": {
                                "footer": {
                                    "separator": true
                                }
                            }
                        }
                    }
                ]).then(() => {
                    liff.sendMessages([
                        {
                            type: 'text',
                            text: `
                        訂購人：${data['purchaser_name']}
                        到貨日期：${data['arrival_date']}
                        希望到貨時段：${data['arrival_time']}
                        匯款期限：${data['last_money_transfer_date']} 晚上 12 點前，並留言給小編您的帳號後五碼，以方便我們對帳哦！

                        戶名：黃香梅
                        銀行：台新銀行 敦南分行 812
                        帳號：28881003432265

                        如有問題，請在下方留言，我們會儘速爲您服務～
                    `
                        }
                    ]).then(() => {
                        liff.closeWindow()
                    }).catch(err => {
                        alert(err)
                    })
                }).catch(err => {
                    alert(err)
                })
            })
    });
</script>
</body>
</html>
