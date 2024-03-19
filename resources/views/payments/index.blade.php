<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <h5>Текущий баланс</h5>
    @if (cache()->has('balance'))
        <div>{{ cache()->get('balance') }}</div>
    @else
        <div>0</div>
    @endif

    <h2>Пополнить баланс</h2>
    <br>
    <form action="{{ route('payment.create') }}" method="POST">
        @csrf
        <input name="description" type="text" placeholder="Описание платежа">
        <input type="number" placeholder="Сумма" name="amount">
        <button>Оплатить</button>
    </form>
    <br>
    <h2>Список транзакций</h2>
    <table>
        <thead>
            <th>Id</th>
            <th>Сумма</th>
            <th>Описание</th>
            <th>Статус</th>
            <th>Дата</th>
        </thead>
        <tbody>
            @forelse ($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->id }}</td>
                    <td>{{ $transaction->amount }}</td>
                    <td>{{ $transaction->description }}</td>
                    <td>{{ $transaction->status }}</td>
                    <td>{{ $transaction->updated_at->format('d-m-Y H:i') }}</td>
                </tr>
            @empty
            <tr>
                <td>
                    Транзакций нет
                <td>

            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
