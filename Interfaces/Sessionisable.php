<?php
namespace CRUDsader\Interfaces {
    interface Sessionisable {
        function useSession($bool);
        function getSession();
        function resetSession();
    }
}