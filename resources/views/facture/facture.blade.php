<!--<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=h1, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <title>Document</title>

    <style>
        body{
            margin: 0;
        }
        #invoice{
            padding: 30px;
        }
    
        .invoice {
            position: relative;
            background-color: #FFF;
            min-height: 680px;
            padding: 15px
        }
    
        .invoice header {
            padding: 10px 0;
            margin-bottom: 20px;
            border-bottom: 1px solid #3989c6
        }
    
        .invoice .company-details {
            text-align: right
        }
    
        .invoice .company-details .name {
            margin-top: 0;
            margin-bottom: 0;
            /*color: #ff5328*/
            color: #00234C
        }
    
        .invoice .contacts {
            margin-bottom: 20px
        }
    
        .invoice .invoice-to {
            text-align: left
        }
    
        .invoice .invoice-to .to {
            margin-top: 0;
            margin-bottom: 0
        }
    
        .invoice .invoice-details {
            text-align: right
        }
    
        .invoice .invoice-details .invoice-id {
            margin-top: 0;
            /*color: #3989c6*/
            color: #00234C
        }
    
        .invoice main {
            padding-bottom: 50px
        }
    
        .invoice table {
            /*width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            margin-bottom: 20px*/
        }
    
        .invoice table td,.invoice table th {
            padding: 5px;
            background: #eee;
            border-bottom: 1px solid #fff
        }
    
        .invoice table th {
            white-space: nowrap;
            font-weight: 400;
            font-size: 16px;
        }
    
        .invoice table td h3 {
            /*margin: 0;
            font-weight: 400;
            color: #3989c6;
            font-size: 1.2em*/
        }
    
        .invoice table .total {
            background: #3989c6;
            color: #fff
        }
    
        .invoice table tbody tr:last-child td {
            /*border: none*/
        }
    
        .invoice table tfoot td {
            /*background: 0 0;
            border-bottom: none;
            white-space: normal;
            text-align: right;*/
            /*margin-left: -20px;
            padding: 10px 20px;
            font-size: 1.2em;
            border-top: 1px solid #aaa*/
        }
    
        .invoice table tfoot tr:first-child td {
            /*border-top: none*/
        }
    
        .invoice table tfoot tr:last-child td {
            color: #3989c6;
            font-size: 1.1em;
    
        }
    
        .invoice table tfoot tr td:first-child {
            /*border: none*/
        }
    
        .invoice footer {
            width: 100%;
            text-align: center;
            color: #777;
            border-top: 1px solid #aaa;
            padding: 8px 0
        }
    
        @media print {
            .invoice {
                font-size: 11px!important;
                overflow: hidden!important
            }
    
            .invoice footer {
                position: absolute;
                bottom: 10px;
                page-break-after: always
            }
    
            .invoice>div:last-child {
                page-break-before: always
            }
        }
    </style>

</head>
<body>

<div id="invoice">
    <div class="invoice overflow-auto">
        <div style="min-width: 600px">

            <header>
                <div class="row">
                    <div class="col">
                        <a target="_blank" href="">
                            <img src="" data-holder-rendered="true" height="50" />
                        </a>
                    </div>
                    <div class="col company-details">
                        <h2 class="name">Cardio-afrique</h2>
                        <div>Tel : +237 694 899 84</div>
                        <div>Email : contact@edrug-cardio.com</div>
                        <div>Adresse : Douala - rue du marché new-deido <br>(à 150 m de l'école primaire petit monde par quifeurou)</div>
                    </div>
                </div>
            </header>
            <main>
                <div class="row contacts">
                    <div class="col invoice-to">
                        <h2 class="to">client : {{$data['customer']->name}} {{$data['customer']->surname}}</h2>
                    </div>
                    <div class="col invoice-details">
                        <h1 class="invoice-id">Commande : CMD000{{$data['commande']->id}}</h1>
                        <div class="date">Transaction : T00{{$data['transaction']->id}}</div>
                        <div class="date">Date Transaction : {{$data['transaction']->created_at}}</div>
                    </div>
                </div>
                
                
                <table border="0" cellspacing="0" cellpadding="0"  width="460">
                    <thead>
                        <tr>
                            <th class="text-right">Produit</th>
                            <th class="text-right">Catégorie</th>
                            <th class="text-right">Détail</th>
                            <th class="text-right">Quantité</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{--@php $nbRow = 0; @endphp--}}
                        @if ($data['all_paniers']->count())
                            @foreach ($data['all_paniers'] as $item)
                                <tr>
                                    <td class="text-left">{{ $item['panier']->produit->designation }}</td>
                                    <td class="text-left">{{ $item['panier']->produit->categorie->name }}</td>
                                    @if ($item['detail_panier']->count())

                                        {{--@php $nbRow += $item['detail_panier']->count(); @endphp--}}

                                        <td class="text-left" rowspan='".$detail["detail_panier"]->count()."'>
                                            <ul>
                                                
                                                @foreach ($item['detail_panier'] as $detail)
                                                    <li>
                                                        {{ $detail->produit->designation }}
                                                    </li>
                                                @endforeach
                                                
                                            </ul>
                                        </td>
                                
                                    @else
                                        <td class="text-left">/</td>

                                        {{--@php $nbRow += 1; @endphp--}}

                                    @endif

                                    <td class="text-left">{{ $item['panier']->quantite }}</td>
        
                                </tr>
    
                            @endforeach

                        @endif

                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr> 
                            <td class="text-right">Total net à Payer</td>
                            <td class="text-left">{{ $data['montant_a_payer_panier'] }} </td>
                        </tr>
                        <tr> 
                            <td class="text-right">Montant versé</td>
                            <td class="total text-left">{{ $data['transaction']->montant }}</td>
                        </tr>
                        
                        <tr>
                            <td class="text-right">Reste à Payer</td>
                            <td class="text-left">{{ $data['reste_a_payer'] }} XAF</td>
                        </tr>
                    </tfoot>
                </table>
            </main>
            <footer>
                Cardio-afrique
            </footer>
        </div>
        <div></div>
    </div>
</div>

</body>
</html>-->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" >
    <title>Document</title>
</head>
<body>

    <style>

#invoice {
    padding: 300px;
}

.invoice {
    position: relative;
    background-color: #FFF;
    min-height: 680px;
    padding: 15px
}

.invoice header {
    padding: 10px 0;
    margin-bottom: 20px;
    border-bottom: 1px solid #0d6efd
}

.invoice .company-details {
    text-align: right
}

.invoice .company-details .name {
    margin-top: 0;
    margin-bottom: 0
}

.invoice .contacts {
    margin-bottom: 20px
}

.invoice .invoice-to {
    text-align: left
}

.invoice .invoice-to .to {
    margin-top: 0;
    margin-bottom: 0
}

.invoice .invoice-details {
    text-align: right
}

.invoice .invoice-details .invoice-id {
    margin-top: 0;
    color: #0d6efd
}

.invoice main {
    padding-bottom: 50px
}

.invoice main .thanks {
    margin-top: -100px;
    font-size: 2em;
    margin-bottom: 50px
}

.invoice main .notices {
    padding-left: 6px;
    border-left: 6px solid #0d6efd;
    background: #e7f2ff;
    padding: 10px;
}

.invoice main .notices .notice {
    font-size: 1.2em
}

.invoice table {
    width: 100%;
    border-collapse: collapse;
    border-spacing: 0;
    margin-bottom: 20px
}

.invoice table td,
.invoice table th {
    padding: 15px;
    background: #eee;
    border-bottom: 1px solid #fff
}

.invoice table th {
    white-space: nowrap;
    font-weight: 400;
    font-size: 16px
}

.invoice table td h3 {
    margin: 0;
    font-weight: 400;
    
    font-size: 1.2em
}

.invoice table .qty,
.invoice table .total,
.invoice table .unit {
    
    font-size: 1.2em
}

.invoice table .no {
    color: #fff;
    font-size: 1.6em;
    background: #0d6efd
}

.invoice table .unit {
    background: #ddd
}

.invoice table .total {
    background: #0d6efd;
    color: #fff
}

.invoice table tbody tr:last-child td {
    border: none
}

.invoice table tfoot td {
    background: 0 0;
    border-bottom: none;
    white-space: nowrap;
    text-align: right;
    padding: 10px 20px;
    font-size: 1.2em;
    border-top: 1px solid #aaa
}

.invoice table tfoot tr:first-child td {
    border-top: none
}
.card {
    position: relative;
    display: flex;
    flex-direction: column;
    min-width: 0;
    word-wrap: break-word;
    background-color: #fff;
    background-clip: border-box;
    border: 0px solid rgba(0, 0, 0, 0);
    border-radius: .25rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 6px 0 rgb(218 218 253 / 65%), 0 2px 6px 0 rgb(206 206 238 / 54%);
}

.invoice table tfoot tr:last-child td {
    color: #0d6efd;
    font-size: 1.4em;
    border-top: 1px solid #0d6efd
}

.invoice table tfoot tr td:first-child {
    border: none
}

.invoice footer {
    width: 100%;
    text-align: center;
    color: #777;
    border-top: 1px solid #aaa;
    padding: 8px 0
}

@media print {
    .invoice {
        font-size: 11px !important;
        overflow: hidden !important
    }
    .invoice footer {
        position: absolute;
        bottom: 10px;
        page-break-after: always
    }
    .invoice>div:last-child {
        page-break-before: always
    }
}

.invoice main .notices {
    padding-left: 6px;
    border-left: 6px solid #0d6efd;
    background: #e7f2ff;
    padding: 10px;
}
    </style>

    <div class="container">
        <div class="card">
            <div class="card-body">
                <div id="invoice">
                    
                    <div class="invoice overflow-auto">
                        <div style="min-width: 60px">
                            <header>
                                <div class="row">
                                    <div class="col">
                                        <a target="_blank" href="">
                                            <img src="" data-holder-rendered="true" height="50" />
                                        </a>
                                    </div>
                                    <div class="col company-details">
                                        <h2 class="name">Cardio-afrique</h2>
                                        <div>Tel : +237 694 899 84</div>
                                        <div>Email : contact@edrug-cardio.com</div>
                                        <div>Adresse : Douala - rue du marché new-deido <br>(à 150 m de l'école primaire petit monde par quifeurou)</div>
                                    </div>
                                </div>
                            </header>
                            <main>
                                <div class="row contacts">
                                    <div class="col invoice-to">
                                        <h2 class="to">client : {{$data['customer']->name}} {{$data['customer']->surname}}</h2>
                                    </div>
                                    <div class="col invoice-details">
                                        <h1 class="invoice-id">Commande : CMD000{{$data['commande']->id}}</h1>
                                        <div class="date">Transaction : T00{{$data['transaction']->id}}</div>
                                        <div class="date">Date Transaction : {{$data['transaction']->created_at}}</div>
                                    </div>
                                </div>
                                <table>
                                    <thead>
                                        <tr>
                                            <th class="text-left">Produit</th>
                                            <th class="">Détail</th>
                                            <th class="text-left">Catégorie</th>
                                            <th class="text-right">Quantité</th>
                                            <th class="text-right">PU</th>
                                            <th class="text-right">PT</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if ($data['all_paniers']->count())
                                            @foreach ($data['all_paniers'] as $item)

                                                <tr>
                                                    <td class="unit">{{ $item['panier']->produit->designation }}</td>
                                                    @if ($item['detail_panier']->count())

                                                        {{--@php $nbRow += $item['detail_panier']->count(); @endphp--}}

                                                        <td class="" rowspan='".$detail["detail_panier"]->count()."'>
                                                            <ul>
                                                                
                                                                @foreach ($item['detail_panier'] as $detail)
                                                                    <li>
                                                                        {{ $detail->produit->designation }}
                                                                    </li>
                                                                @endforeach
                                                                
                                                            </ul>
                                                        </td>
                                
                                                    @else
                                                        <td class="">/</td>

                                                        {{--@php $nbRow += 1; @endphp--}}

                                                    @endif
                                                    <td class="unit">{{ $item['panier']->produit->categorie->name }}</td>
                                                    <td class="text-left">{{ $item['panier']->quantite }}</td>
                                                    <td class="qty">{{ $item['panier']->->produit->prix_produit }} XAF</td>
                                                    <td class="total">{{ $item['panier']->prix_total }} XAF</td>
                                                </tr>

                                            @endforeach
                                        @endif    
                                        
                                    </tbody>
                                    <tfoot >
                                        <tr>
                                            <td colspan="3"></td>
                                            <td >Total net à Payer</td>
                                            <td colspan="3">{{ $data['montant_a_payer_panier'] }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="3"></td>
                                            <td >Montant versé</td>
                                            <td colspan="3">{{ $data['transaction']->montant }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="3"></td>
                                            <td >Reste à Payer</td>
                                            <td colspan="3">{{ $data['reste_a_payer'] }} XAF</td>
                                        </tr>
                                    </tfoot>
                                </table>
                                
                            </main>
                            <footer>Cardio-afrique.</footer>
                        </div>
                        <div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>


