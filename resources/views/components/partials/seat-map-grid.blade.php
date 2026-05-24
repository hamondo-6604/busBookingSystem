@props(['seatMap', 'seatClassPrefix' => 'seat-sheet'])

<div class="overflow-x-auto pb-2">
    <div class="{{ $seatClassPrefix }}-bus">
        <div class="{{ $seatClassPrefix }}-bus-front"></div>
        <div class="{{ $seatClassPrefix }}-steering"></div>
        <div class="flex flex-col gap-3 mt-6">
            @foreach($seatMap as $rowIndex => $row)
                <div class="flex gap-3 justify-center"
                     style="{{ $rowIndex === (count($seatMap) - 1) ? 'column-gap: 10px;' : '' }}">
                    @php
                        $isLastRow = $rowIndex === (count($seatMap) - 1);
                        $renderRow = $row;
                        $bookableSeatsInRow = collect($row)->filter(function ($rowCell) {
                            return ($rowCell['cell_type'] ?? 'empty') === 'seat'
                                && ($rowCell['is_bookable'] ?? false);
                        })->values();
                        if ($isLastRow && $bookableSeatsInRow->count() === 4) {
                            $lastSeatLabel = $bookableSeatsInRow->last()['seat_label'] ?? '';
                            $rearExtraLabel = '';
                            if (preg_match('/^(\d+)([A-Z])$/', $lastSeatLabel, $matches)) {
                                $rearExtraLabel = $matches[1] . chr(ord($matches[2]) + 1);
                            }
                            array_splice($renderRow, 2, 0, [[
                                'cell_type' => 'seat', 'is_bookable' => true, 'is_available' => false,
                                'is_own_booking' => false, 'seat_label' => $rearExtraLabel, 'fare' => 0,
                            ]]);
                        }
                        $rowCount = count($renderRow);
                        $hasExplicitAisle = collect($row)->contains(fn ($c) => ($c['cell_type'] ?? 'empty') === 'aisle');
                        $hasInlineGapPlaceholder = false;
                        foreach ($renderRow as $idx => $rowCell) {
                            if (($rowCell['cell_type'] ?? 'empty') !== 'empty') continue;
                            $prevType = $idx > 0 ? ($renderRow[$idx - 1]['cell_type'] ?? 'empty') : null;
                            $nextType = $idx < ($rowCount - 1) ? ($renderRow[$idx + 1]['cell_type'] ?? 'empty') : null;
                            if ($prevType === 'seat' && $nextType === 'seat') { $hasInlineGapPlaceholder = true; break; }
                        }
                    @endphp
                    @foreach($renderRow as $cellIndex => $cell)
                        @if(! $isLastRow && ! $hasExplicitAisle && ! $hasInlineGapPlaceholder && $cellIndex === 2)
                            <div class="{{ $seatClassPrefix }}-cell-aisle"></div>
                        @endif
                        @php
                            $type = $cell['cell_type'] ?? 'empty';
                            $isBookable = $cell['is_bookable'] ?? false;
                            $isAvailable = $cell['is_available'] ?? false;
                            $isOwnBooking = $cell['is_own_booking'] ?? false;
                            $label = $cell['seat_label'] ?? '';
                            $fare = $cell['fare'] ?? 0;
                            $seatClass = $isAvailable ? 'available' : ($isOwnBooking ? 'own-booking' : 'occupied');
                        @endphp
                        @if($type === 'seat' && $isBookable)
                            <div class="{{ $seatClassPrefix }}-seat {{ $seatClass }}"
                                 data-seat="{{ $label }}"
                                 data-fare="{{ $fare }}"
                                 @if($isAvailable) onclick="SeatSheet.toggleSeat(this)" @endif
                                 title="{{ $isAvailable ? 'Seat ' . $label . ' - ₱' . number_format($fare, 0) : ($isOwnBooking ? 'Your seat' : 'Occupied') }}">
                                <span class="text-[9px] leading-none mb-0.5">{{ $label }}</span>
                                @if($isAvailable && $fare > 0)
                                    <span class="text-[8px] font-medium opacity-70">₱{{ number_format($fare, 0) }}</span>
                                @endif
                            </div>
                        @elseif($type === 'driver')
                            <div class="{{ $seatClassPrefix }}-cell-driver">
                                <i data-lucide="circle-dot" style="width:18px;height:18px"></i>
                            </div>
                        @elseif($type === 'door')
                            <div class="{{ $seatClassPrefix }}-cell-door">Door</div>
                        @elseif($type === 'stairs')
                            <div class="{{ $seatClassPrefix }}-cell-door">Stairs</div>
                        @elseif($type === 'aisle' && ! $isLastRow)
                            <div class="{{ $seatClassPrefix }}-cell-aisle"></div>
                        @elseif(! $isLastRow && $type === 'empty' && $cellIndex > 0 && $cellIndex < ($rowCount - 1)
                            && (($renderRow[$cellIndex - 1]['cell_type'] ?? 'empty') === 'seat')
                            && (($renderRow[$cellIndex + 1]['cell_type'] ?? 'empty') === 'seat'))
                            <div class="{{ $seatClassPrefix }}-cell-aisle"></div>
                        @else
                            <div class="{{ $seatClassPrefix }}-cell-empty"></div>
                        @endif
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</div>
